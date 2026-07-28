<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiKhuVuc;
use App\Http\Controllers\Controller;
use App\Models\KhuVuc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KhuVucController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = KhuVuc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_khu_vuc', 'like', "%{$keyword}%")
                            ->orWhere('ma_khu_vuc', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiKhuVuc::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('ten_khu_vuc');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách khu vực thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(KhuVuc $khuVuc): JsonResponse
    {
        return $this->tryApi(function () use ($khuVuc) {
            return ApiResponse::success($khuVuc, 'Lấy chi tiết khu vực thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_khu_vuc' => ['required', 'string', 'max:255'],
                'ma_khu_vuc' => ['required', 'string', 'max:50', 'unique:danh_muc_khu_vuc,ma_khu_vuc'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['required', Rule::enum(TrangThaiKhuVuc::class)],
            ]);

            $item = KhuVuc::create($validated);

            return ApiResponse::success($item, 'Tạo khu vực thành công.');
        });
    }

    public function update(Request $request, KhuVuc $khuVuc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $khuVuc) {
            $validated = $request->validate([
                'ten_khu_vuc' => ['sometimes', 'string', 'max:255'],
                'ma_khu_vuc' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_khu_vuc', 'ma_khu_vuc')->ignore($khuVuc->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiKhuVuc::class)],
            ]);

            $khuVuc->update($validated);

            return ApiResponse::success($khuVuc->fresh(), 'Cập nhật khu vực thành công.');
        });
    }

    public function destroy(KhuVuc $khuVuc): JsonResponse
    {
        return $this->tryApi(function () use ($khuVuc) {
            $khuVuc->delete();

            return ApiResponse::success(null, 'Đã xóa khu vực.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_khu_vuc,id'],
            ]);

            $count = KhuVuc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} khu vực.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_khu_vuc,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiKhuVuc::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiKhuVuc
                ? $validated['trang_thai']
                : TrangThaiKhuVuc::from($validated['trang_thai']);

            $count = KhuVuc::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} khu vực.",
            );
        });
    }
}
