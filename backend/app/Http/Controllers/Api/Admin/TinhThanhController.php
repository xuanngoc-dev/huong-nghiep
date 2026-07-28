<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiTinhThanh;
use App\Http\Controllers\Controller;
use App\Models\TinhThanh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TinhThanhController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $khuVucId = $request->query('khu_vuc_id');

            $query = TinhThanh::query()
                ->with(['khuVuc:id,ma_khu_vuc,ten_khu_vuc'])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_tinh_thanh', 'like', "%{$keyword}%")
                            ->orWhere('ma_tinh_thanh', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiTinhThanh::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    filled($khuVucId) && is_numeric($khuVucId),
                    fn ($query) => $query->where('khu_vuc_id', (int) $khuVucId),
                )
                ->orderByRaw('CAST(ma_tinh_thanh AS UNSIGNED)')
                ->orderBy('ten_tinh_thanh');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách tỉnh thành thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TinhThanh $tinhThanh): JsonResponse
    {
        return $this->tryApi(function () use ($tinhThanh) {
            $tinhThanh->load(['khuVuc:id,ma_khu_vuc,ten_khu_vuc']);

            return ApiResponse::success($tinhThanh, 'Lấy chi tiết tỉnh thành thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_tinh_thanh' => ['required', 'string', 'max:255'],
                'ma_tinh_thanh' => ['required', 'string', 'max:20', 'unique:danh_muc_tinh_thanh,ma_tinh_thanh'],
                'khu_vuc_id' => ['nullable', 'integer', 'exists:danh_muc_khu_vuc,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTinhThanh::class)],
            ]);

            $item = TinhThanh::create($validated);
            $item->load(['khuVuc:id,ma_khu_vuc,ten_khu_vuc']);

            return ApiResponse::success($item, 'Tạo tỉnh thành thành công.');
        });
    }

    public function update(Request $request, TinhThanh $tinhThanh): JsonResponse
    {
        return $this->tryApi(function () use ($request, $tinhThanh) {
            $validated = $request->validate([
                'ten_tinh_thanh' => ['sometimes', 'string', 'max:255'],
                'ma_tinh_thanh' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('danh_muc_tinh_thanh', 'ma_tinh_thanh')->ignore($tinhThanh->id),
                ],
                'khu_vuc_id' => ['nullable', 'integer', 'exists:danh_muc_khu_vuc,id'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiTinhThanh::class)],
            ]);

            $tinhThanh->update($validated);

            return ApiResponse::success(
                $tinhThanh->fresh(['khuVuc:id,ma_khu_vuc,ten_khu_vuc']),
                'Cập nhật tỉnh thành thành công.',
            );
        });
    }

    public function destroy(TinhThanh $tinhThanh): JsonResponse
    {
        return $this->tryApi(function () use ($tinhThanh) {
            $tinhThanh->delete();

            return ApiResponse::success(null, 'Đã xóa tỉnh thành.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_tinh_thanh,id'],
            ]);

            $count = TinhThanh::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} tỉnh thành.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_tinh_thanh,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTinhThanh::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiTinhThanh
                ? $validated['trang_thai']
                : TrangThaiTinhThanh::from($validated['trang_thai']);

            $count = TinhThanh::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} tỉnh thành.",
            );
        });
    }
}
