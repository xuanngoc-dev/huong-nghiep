<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiPhuongXa;
use App\Http\Controllers\Controller;
use App\Models\PhuongXa;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhuongXaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $maTinhThanh = trim((string) $request->query('ma_tinh_thanh', ''));

            $query = PhuongXa::query()
                ->with(['tinhThanh:id,ma_tinh_thanh,ten_tinh_thanh'])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_phuong_xa', 'like', "%{$keyword}%")
                            ->orWhere('ma_phuong_xa', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiPhuongXa::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    $maTinhThanh !== '',
                    fn ($query) => $query->where('ma_tinh_thanh', $maTinhThanh),
                )
                ->orderByRaw('CAST(ma_tinh_thanh AS UNSIGNED)')
                ->orderByRaw('CAST(ma_phuong_xa AS UNSIGNED)')
                ->orderBy('ten_phuong_xa');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách phường xã thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(PhuongXa $phuongXa): JsonResponse
    {
        return $this->tryApi(function () use ($phuongXa) {
            $phuongXa->load(['tinhThanh:id,ma_tinh_thanh,ten_tinh_thanh']);

            return ApiResponse::success($phuongXa, 'Lấy chi tiết phường xã thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_phuong_xa' => ['required', 'string', 'max:255'],
                'ma_phuong_xa' => ['required', 'string', 'max:20', 'unique:danh_muc_phuong_xa,ma_phuong_xa'],
                'ma_tinh_thanh' => ['required', 'string', 'max:20', 'exists:danh_muc_tinh_thanh,ma_tinh_thanh'],
                'trang_thai' => ['required', Rule::enum(TrangThaiPhuongXa::class)],
            ]);

            $item = PhuongXa::create($validated);
            $item->load(['tinhThanh:id,ma_tinh_thanh,ten_tinh_thanh']);

            return ApiResponse::success($item, 'Tạo phường xã thành công.');
        });
    }

    public function update(Request $request, PhuongXa $phuongXa): JsonResponse
    {
        return $this->tryApi(function () use ($request, $phuongXa) {
            $validated = $request->validate([
                'ten_phuong_xa' => ['sometimes', 'string', 'max:255'],
                'ma_phuong_xa' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('danh_muc_phuong_xa', 'ma_phuong_xa')->ignore($phuongXa->id),
                ],
                'ma_tinh_thanh' => ['sometimes', 'string', 'max:20', 'exists:danh_muc_tinh_thanh,ma_tinh_thanh'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiPhuongXa::class)],
            ]);

            $phuongXa->update($validated);

            return ApiResponse::success(
                $phuongXa->fresh(['tinhThanh:id,ma_tinh_thanh,ten_tinh_thanh']),
                'Cập nhật phường xã thành công.',
            );
        });
    }

    public function destroy(PhuongXa $phuongXa): JsonResponse
    {
        return $this->tryApi(function () use ($phuongXa) {
            $phuongXa->delete();

            return ApiResponse::success(null, 'Đã xóa phường xã.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_phuong_xa,id'],
            ]);

            $count = PhuongXa::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} phường xã.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_phuong_xa,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiPhuongXa::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiPhuongXa
                ? $validated['trang_thai']
                : TrangThaiPhuongXa::from($validated['trang_thai']);

            $count = PhuongXa::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} phường xã.",
            );
        });
    }
}
