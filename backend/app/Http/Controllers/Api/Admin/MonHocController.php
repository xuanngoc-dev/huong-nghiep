<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonHoc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonHocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = MonHoc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_mon_hoc', 'like', "%{$keyword}%")
                            ->orWhere('ma_mon_hoc', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_mon_hoc');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách môn học thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(MonHoc $monHoc): JsonResponse
    {
        return $this->tryApi(function () use ($monHoc) {
            return ApiResponse::success($monHoc, 'Lấy chi tiết môn học thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_mon_hoc' => ['required', 'string', 'max:255'],
                'ma_mon_hoc' => ['required', 'string', 'max:50', 'unique:danh_muc_mon_hoc,ma_mon_hoc'],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $item = MonHoc::create($validated);

            return ApiResponse::success($item, 'Tạo môn học thành công.');
        });
    }

    public function update(Request $request, MonHoc $monHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $monHoc) {
            $validated = $request->validate([
                'ten_mon_hoc' => ['sometimes', 'string', 'max:255'],
                'ma_mon_hoc' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_mon_hoc', 'ma_mon_hoc')->ignore($monHoc->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $monHoc->update($validated);

            return ApiResponse::success($monHoc->fresh(), 'Cập nhật môn học thành công.');
        });
    }

    public function destroy(MonHoc $monHoc): JsonResponse
    {
        return $this->tryApi(function () use ($monHoc) {
            $monHoc->delete();

            return ApiResponse::success(null, 'Đã xóa môn học.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_mon_hoc,id'],
            ]);

            $count = MonHoc::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} môn học.",
            );
        });
    }
}
