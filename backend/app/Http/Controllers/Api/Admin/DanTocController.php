<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiDanToc;
use App\Http\Controllers\Controller;
use App\Models\DanToc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DanTocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = DanToc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_dan_toc', 'like', "%{$keyword}%")
                            ->orWhere('ma_dan_toc', 'like', "%{$keyword}%")
                            ->orWhere('ten_goi_khac', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiDanToc::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('ten_dan_toc');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách dân tộc thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(DanToc $danToc): JsonResponse
    {
        return $this->tryApi(function () use ($danToc) {
            return ApiResponse::success($danToc, 'Lấy chi tiết dân tộc thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_dan_toc' => ['required', 'string', 'max:255'],
                'ma_dan_toc' => ['required', 'string', 'max:50', 'unique:danh_muc_dan_toc,ma_dan_toc'],
                'ten_goi_khac' => ['nullable', 'string', 'max:255'],
                'trang_thai' => ['required', Rule::enum(TrangThaiDanToc::class)],
            ]);

            $item = DanToc::create($validated);

            return ApiResponse::success($item, 'Tạo dân tộc thành công.');
        });
    }

    public function update(Request $request, DanToc $danToc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $danToc) {
            $validated = $request->validate([
                'ten_dan_toc' => ['sometimes', 'string', 'max:255'],
                'ma_dan_toc' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_dan_toc', 'ma_dan_toc')->ignore($danToc->id),
                ],
                'ten_goi_khac' => ['nullable', 'string', 'max:255'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiDanToc::class)],
            ]);

            $danToc->update($validated);

            return ApiResponse::success($danToc->fresh(), 'Cập nhật dân tộc thành công.');
        });
    }

    public function destroy(DanToc $danToc): JsonResponse
    {
        return $this->tryApi(function () use ($danToc) {
            $danToc->delete();

            return ApiResponse::success(null, 'Đã xóa dân tộc.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_dan_toc,id'],
            ]);

            $count = DanToc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} dân tộc.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_dan_toc,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiDanToc::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiDanToc
                ? $validated['trang_thai']
                : TrangThaiDanToc::from($validated['trang_thai']);

            $count = DanToc::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} dân tộc.",
            );
        });
    }
}
