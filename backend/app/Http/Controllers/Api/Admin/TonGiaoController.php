<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiTonGiao;
use App\Http\Controllers\Controller;
use App\Models\TonGiao;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TonGiaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = TonGiao::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_ton_giao', 'like', "%{$keyword}%")
                            ->orWhere('ma_ton_giao', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiTonGiao::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('ten_ton_giao');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách tôn giáo thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TonGiao $tonGiao): JsonResponse
    {
        return $this->tryApi(function () use ($tonGiao) {
            return ApiResponse::success($tonGiao, 'Lấy chi tiết tôn giáo thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_ton_giao' => ['required', 'string', 'max:255'],
                'ma_ton_giao' => ['required', 'string', 'max:50', 'unique:danh_muc_ton_giao,ma_ton_giao'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTonGiao::class)],
            ]);

            $item = TonGiao::create($validated);

            return ApiResponse::success($item, 'Tạo tôn giáo thành công.');
        });
    }

    public function update(Request $request, TonGiao $tonGiao): JsonResponse
    {
        return $this->tryApi(function () use ($request, $tonGiao) {
            $validated = $request->validate([
                'ten_ton_giao' => ['sometimes', 'string', 'max:255'],
                'ma_ton_giao' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_ton_giao', 'ma_ton_giao')->ignore($tonGiao->id),
                ],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiTonGiao::class)],
            ]);

            $tonGiao->update($validated);

            return ApiResponse::success($tonGiao->fresh(), 'Cập nhật tôn giáo thành công.');
        });
    }

    public function destroy(TonGiao $tonGiao): JsonResponse
    {
        return $this->tryApi(function () use ($tonGiao) {
            $tonGiao->delete();

            return ApiResponse::success(null, 'Đã xóa tôn giáo.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_ton_giao,id'],
            ]);

            $count = TonGiao::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} tôn giáo.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_ton_giao,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiTonGiao::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiTonGiao
                ? $validated['trang_thai']
                : TrangThaiTonGiao::from($validated['trang_thai']);

            $count = TonGiao::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} tôn giáo.",
            );
        });
    }
}
