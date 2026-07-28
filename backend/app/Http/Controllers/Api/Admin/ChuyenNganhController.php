<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiNganhHoc;
use App\Http\Controllers\Controller;
use App\Models\ChuyenNganh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChuyenNganhController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $nganhHocId = $request->query('nganh_hoc_id');

            $query = ChuyenNganh::query()
                ->with(['nganhHoc:id,ma_nganh,ten_nganh'])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_chuyen_nganh', 'like', "%{$keyword}%")
                            ->orWhere('ma_chuyen_nganh', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiNganhHoc::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    filled($nganhHocId) && is_numeric($nganhHocId),
                    fn ($query) => $query->where('nganh_hoc_id', (int) $nganhHocId),
                )
                ->orderBy('ten_chuyen_nganh');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách chuyên ngành thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(ChuyenNganh $chuyenNganh): JsonResponse
    {
        return $this->tryApi(function () use ($chuyenNganh) {
            $chuyenNganh->load(['nganhHoc:id,ma_nganh,ten_nganh']);

            return ApiResponse::success($chuyenNganh, 'Lấy chi tiết chuyên ngành thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ma_chuyen_nganh' => ['required', 'string', 'max:50', 'unique:danh_muc_chuyen_nganh,ma_chuyen_nganh'],
                'ten_chuyen_nganh' => ['required', 'string', 'max:255'],
                'nganh_hoc_id' => ['required', 'integer', 'exists:danh_muc_nganh_hoc,id'],
                'mo_ta' => ['nullable', 'string'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $item = ChuyenNganh::create($validated);
            $item->load(['nganhHoc:id,ma_nganh,ten_nganh']);

            return ApiResponse::success($item, 'Tạo chuyên ngành thành công.');
        });
    }

    public function update(Request $request, ChuyenNganh $chuyenNganh): JsonResponse
    {
        return $this->tryApi(function () use ($request, $chuyenNganh) {
            $validated = $request->validate([
                'ma_chuyen_nganh' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_chuyen_nganh', 'ma_chuyen_nganh')->ignore($chuyenNganh->id),
                ],
                'ten_chuyen_nganh' => ['sometimes', 'string', 'max:255'],
                'nganh_hoc_id' => ['sometimes', 'integer', 'exists:danh_muc_nganh_hoc,id'],
                'mo_ta' => ['nullable', 'string'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $chuyenNganh->update($validated);
            $chuyenNganh->load(['nganhHoc:id,ma_nganh,ten_nganh']);

            return ApiResponse::success($chuyenNganh->fresh(['nganhHoc:id,ma_nganh,ten_nganh']), 'Cập nhật chuyên ngành thành công.');
        });
    }

    public function destroy(ChuyenNganh $chuyenNganh): JsonResponse
    {
        return $this->tryApi(function () use ($chuyenNganh) {
            $chuyenNganh->delete();

            return ApiResponse::success(null, 'Đã xóa chuyên ngành.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_chuyen_nganh,id'],
            ]);

            $count = ChuyenNganh::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} chuyên ngành.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_chuyen_nganh,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiNganhHoc
                ? $validated['trang_thai']
                : TrangThaiNganhHoc::from($validated['trang_thai']);

            $count = ChuyenNganh::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} chuyên ngành.",
            );
        });
    }
}
