<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiTruong;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoaiTruongController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = LoaiTruong::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_loai_truong', 'like', "%{$keyword}%")
                            ->orWhere('ma_loai_truong', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_loai_truong');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách loại trường thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(LoaiTruong $loaiTruong): JsonResponse
    {
        return $this->tryApi(function () use ($loaiTruong) {
            return ApiResponse::success($loaiTruong, 'Lấy chi tiết loại trường thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_loai_truong' => ['required', 'string', 'max:255'],
                'ma_loai_truong' => ['required', 'string', 'max:50', 'unique:danh_muc_loai_truong,ma_loai_truong'],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $item = LoaiTruong::create($validated);

            return ApiResponse::success($item, 'Tạo loại trường thành công.');
        });
    }

    public function update(Request $request, LoaiTruong $loaiTruong): JsonResponse
    {
        return $this->tryApi(function () use ($request, $loaiTruong) {
            $validated = $request->validate([
                'ten_loai_truong' => ['sometimes', 'string', 'max:255'],
                'ma_loai_truong' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_loai_truong', 'ma_loai_truong')->ignore($loaiTruong->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $loaiTruong->update($validated);

            return ApiResponse::success($loaiTruong->fresh(), 'Cập nhật loại trường thành công.');
        });
    }

    public function destroy(LoaiTruong $loaiTruong): JsonResponse
    {
        return $this->tryApi(function () use ($loaiTruong) {
            $loaiTruong->delete();

            return ApiResponse::success(null, 'Đã xóa loại trường.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_loai_truong,id'],
            ]);

            $count = LoaiTruong::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} loại trường.",
            );
        });
    }
}
