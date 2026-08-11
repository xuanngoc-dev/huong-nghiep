<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChuyenNganh;
use App\Models\TruongHocTuyenSinhTheoNam;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TruongHocTuyenSinhTheoNamController extends Controller
{
    private const RELATION_LOAD = [
        'truongHoc:id,ma_truong,ten_truong',
        'nganhHocTuyenSinh:id,ma_nganh,ten_nganh',
        'chuyenNganhTuyenSinh:id,ma_chuyen_nganh,ten_chuyen_nganh,nganh_hoc_id',
    ];

    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $maTruong = trim((string) $request->query('ma_truong', ''));
            $namHoc = trim((string) $request->query('nam_hoc', ''));
            $nganhHocId = $request->query('nganh_hoc_tuyen_sinh_id');

            $query = TruongHocTuyenSinhTheoNam::query()
                ->with(self::RELATION_LOAD)
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ma_truong', 'like', "%{$keyword}%")
                            ->orWhere('nam_hoc', 'like', "%{$keyword}%")
                            ->orWhere('phuong_thuc_xet_tuyen', 'like', "%{$keyword}%")
                            ->orWhere('to_hop_xet_tuyen', 'like', "%{$keyword}%")
                            ->orWhereHas('truongHoc', function ($truong) use ($keyword) {
                                $truong->where('ten_truong', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('nganhHocTuyenSinh', function ($nganh) use ($keyword) {
                                $nganh->where('ten_nganh', 'like', "%{$keyword}%")
                                    ->orWhere('ma_nganh', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('chuyenNganhTuyenSinh', function ($chuyen) use ($keyword) {
                                $chuyen->where('ten_chuyen_nganh', 'like', "%{$keyword}%")
                                    ->orWhere('ma_chuyen_nganh', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->when($maTruong !== '', fn ($query) => $query->where('ma_truong', $maTruong))
                ->when($namHoc !== '', fn ($query) => $query->where('nam_hoc', $namHoc))
                ->when(
                    filled($nganhHocId) && is_numeric($nganhHocId),
                    fn ($query) => $query->where('nganh_hoc_tuyen_sinh_id', (int) $nganhHocId),
                )
                ->orderByDesc('nam_hoc')
                ->orderBy('ma_truong');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách tuyển sinh theo năm thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TruongHocTuyenSinhTheoNam $truongHocTuyenSinhTheoNam): JsonResponse
    {
        return $this->tryApi(function () use ($truongHocTuyenSinhTheoNam) {
            $truongHocTuyenSinhTheoNam->load(self::RELATION_LOAD);

            return ApiResponse::success($truongHocTuyenSinhTheoNam, 'Lấy chi tiết tuyển sinh theo năm thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $this->validatePayload($request);
            $this->assertChuyenNganhBelongsToNganh(
                $validated['chuyen_nganh_tuyen_sinh_id'] ?? null,
                (int) $validated['nganh_hoc_tuyen_sinh_id'],
            );

            $item = TruongHocTuyenSinhTheoNam::create($validated);
            $item->load(self::RELATION_LOAD);

            return ApiResponse::success($item, 'Tạo tuyển sinh theo năm thành công.');
        });
    }

    public function update(Request $request, TruongHocTuyenSinhTheoNam $truongHocTuyenSinhTheoNam): JsonResponse
    {
        return $this->tryApi(function () use ($request, $truongHocTuyenSinhTheoNam) {
            $validated = $this->validatePayload($request, $truongHocTuyenSinhTheoNam);

            $nganhId = (int) ($validated['nganh_hoc_tuyen_sinh_id']
                ?? $truongHocTuyenSinhTheoNam->nganh_hoc_tuyen_sinh_id);
            $chuyenId = array_key_exists('chuyen_nganh_tuyen_sinh_id', $validated)
                ? $validated['chuyen_nganh_tuyen_sinh_id']
                : $truongHocTuyenSinhTheoNam->chuyen_nganh_tuyen_sinh_id;

            $this->assertChuyenNganhBelongsToNganh($chuyenId, $nganhId);

            $truongHocTuyenSinhTheoNam->update($validated);

            return ApiResponse::success(
                $truongHocTuyenSinhTheoNam->fresh(self::RELATION_LOAD),
                'Cập nhật tuyển sinh theo năm thành công.',
            );
        });
    }

    public function destroy(TruongHocTuyenSinhTheoNam $truongHocTuyenSinhTheoNam): JsonResponse
    {
        return $this->tryApi(function () use ($truongHocTuyenSinhTheoNam) {
            $truongHocTuyenSinhTheoNam->delete();

            return ApiResponse::success(null, 'Đã xóa tuyển sinh theo năm.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:truong_hoc_tuyen_sinh_theo_nam,id'],
            ]);

            $count = TruongHocTuyenSinhTheoNam::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} bản ghi tuyển sinh theo năm.",
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?TruongHocTuyenSinhTheoNam $item = null): array
    {
        $isUpdate = $item !== null;
        $required = $isUpdate ? 'sometimes' : 'required';

        $maTruong = $request->input('ma_truong', $item?->ma_truong);
        $namHoc = $request->input('nam_hoc', $item?->nam_hoc);
        $nganhHocId = $request->input('nganh_hoc_tuyen_sinh_id', $item?->nganh_hoc_tuyen_sinh_id);
        $chuyenNganhId = $request->input('chuyen_nganh_tuyen_sinh_id', $item?->chuyen_nganh_tuyen_sinh_id);

        return $request->validate([
            'ma_truong' => [
                $required,
                'string',
                'max:50',
                'exists:danh_muc_truong_hoc,ma_truong',
            ],
            'nam_hoc' => [
                $required,
                'string',
                'max:20',
                Rule::unique('truong_hoc_tuyen_sinh_theo_nam', 'nam_hoc')
                    ->where(fn ($query) => $query
                        ->where('ma_truong', $maTruong)
                        ->where('nganh_hoc_tuyen_sinh_id', $nganhHocId)
                        ->where('chuyen_nganh_tuyen_sinh_id', $chuyenNganhId))
                    ->ignore($item?->id),
            ],
            'nganh_hoc_tuyen_sinh_id' => [
                $required,
                'integer',
                'exists:danh_muc_nganh_hoc,id',
            ],
            'chuyen_nganh_tuyen_sinh_id' => [
                'nullable',
                'integer',
                'exists:danh_muc_chuyen_nganh,id',
            ],
            'phuong_thuc_xet_tuyen' => ['nullable', 'string', 'max:255'],
            'to_hop_xet_tuyen' => ['nullable', 'string', 'max:255'],
            'chi_tieu' => ['nullable', 'integer', 'min:0'],
            'diem_chuan' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'ghi_chu' => ['nullable', 'string'],
        ], [
            'nam_hoc.unique' => 'Trường này đã có ngành/chuyên ngành tuyển sinh cho năm học đã chọn.',
        ]);
    }

    private function assertChuyenNganhBelongsToNganh(mixed $chuyenNganhId, int $nganhHocId): void
    {
        if ($chuyenNganhId === null || $chuyenNganhId === '') {
            return;
        }

        $exists = ChuyenNganh::query()
            ->where('id', (int) $chuyenNganhId)
            ->where('nganh_hoc_id', $nganhHocId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'chuyen_nganh_tuyen_sinh_id' => [
                    'Chuyên ngành phải thuộc ngành học tuyển sinh đã chọn.',
                ],
            ]);
        }
    }
}
