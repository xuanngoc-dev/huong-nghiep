<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiTracNghiemCauHoi;
use App\Http\Controllers\Controller;
use App\Models\TracNghiemCauHoi;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TracNghiemCauHoiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $nganhHocId = $request->query('nganh_hoc_id');
            $chuyenNganhId = $request->query('chuyen_nganh_id');
            $loaiCauHoiId = $request->query('loai_cau_hoi_id');

            $query = TracNghiemCauHoi::query()
                ->with([
                    'nganhHoc:id,ma_nganh,ten_nganh',
                    'chuyenNganh:id,ma_chuyen_nganh,ten_chuyen_nganh',
                    'loaiCauHoi:id,ma_loai_cau_hoi,ten_loai_cau_hoi',
                ])
                ->withCount('cauTraLois')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('noi_dung_cau_hoi', 'like', "%{$keyword}%")
                            ->orWhere('ghi_chu', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiTracNghiemCauHoi::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    filled($nganhHocId) && is_numeric($nganhHocId),
                    fn ($query) => $query->where('nganh_hoc_id', (int) $nganhHocId),
                )
                ->when(
                    filled($chuyenNganhId) && is_numeric($chuyenNganhId),
                    fn ($query) => $query->where('chuyen_nganh_id', (int) $chuyenNganhId),
                )
                ->when(
                    filled($loaiCauHoiId) && is_numeric($loaiCauHoiId),
                    fn ($query) => $query->where('loai_cau_hoi_id', (int) $loaiCauHoiId),
                )
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách câu hỏi thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TracNghiemCauHoi $tracNghiemCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($tracNghiemCauHoi) {
            $tracNghiemCauHoi->load([
                'nganhHoc:id,ma_nganh,ten_nganh',
                'chuyenNganh:id,ma_chuyen_nganh,ten_chuyen_nganh',
                'loaiCauHoi:id,ma_loai_cau_hoi,ten_loai_cau_hoi',
                'cauTraLois' => fn ($q) => $q->orderBy('id'),
            ]);

            return ApiResponse::success($tracNghiemCauHoi, 'Lấy chi tiết câu hỏi thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'nganh_hoc_id' => ['required', 'integer', 'exists:danh_muc_nganh_hoc,id'],
                'chuyen_nganh_id' => ['required', 'integer', 'exists:danh_muc_chuyen_nganh,id'],
                'loai_cau_hoi_id' => ['required', 'integer', 'exists:danh_muc_loai_cau_hoi,id'],
                'noi_dung_cau_hoi' => ['required', 'string'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTracNghiemCauHoi::class)],
            ]);

            $item = TracNghiemCauHoi::create($validated);
            $item->load([
                'nganhHoc:id,ma_nganh,ten_nganh',
                'chuyenNganh:id,ma_chuyen_nganh,ten_chuyen_nganh',
                'loaiCauHoi:id,ma_loai_cau_hoi,ten_loai_cau_hoi',
            ]);

            return ApiResponse::success($item, 'Tạo câu hỏi thành công.');
        });
    }

    public function update(Request $request, TracNghiemCauHoi $tracNghiemCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($request, $tracNghiemCauHoi) {
            $validated = $request->validate([
                'nganh_hoc_id' => ['sometimes', 'integer', 'exists:danh_muc_nganh_hoc,id'],
                'chuyen_nganh_id' => ['sometimes', 'integer', 'exists:danh_muc_chuyen_nganh,id'],
                'loai_cau_hoi_id' => ['sometimes', 'integer', 'exists:danh_muc_loai_cau_hoi,id'],
                'noi_dung_cau_hoi' => ['sometimes', 'string'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiTracNghiemCauHoi::class)],
            ]);

            $tracNghiemCauHoi->update($validated);

            return ApiResponse::success(
                $tracNghiemCauHoi->fresh([
                    'nganhHoc:id,ma_nganh,ten_nganh',
                    'chuyenNganh:id,ma_chuyen_nganh,ten_chuyen_nganh',
                    'loaiCauHoi:id,ma_loai_cau_hoi,ten_loai_cau_hoi',
                    'cauTraLois',
                ]),
                'Cập nhật câu hỏi thành công.',
            );
        });
    }

    public function destroy(TracNghiemCauHoi $tracNghiemCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($tracNghiemCauHoi) {
            $tracNghiemCauHoi->delete();

            return ApiResponse::success(null, 'Đã xóa câu hỏi.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:trac_nghiem_cau_hoi,id'],
            ]);

            $count = TracNghiemCauHoi::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} câu hỏi.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:trac_nghiem_cau_hoi,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTracNghiemCauHoi::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiTracNghiemCauHoi
                ? $validated['trang_thai']
                : TrangThaiTracNghiemCauHoi::from($validated['trang_thai']);

            $count = TracNghiemCauHoi::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} câu hỏi.",
            );
        });
    }
}
