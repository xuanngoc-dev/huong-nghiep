<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhuongThucXetTuyen;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhuongThucXetTuyenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = PhuongThucXetTuyen::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_phuong_thuc', 'like', "%{$keyword}%")
                            ->orWhere('ma_phuong_thuc', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_phuong_thuc');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách phương thức xét tuyển thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(PhuongThucXetTuyen $phuongThucXetTuyen): JsonResponse
    {
        return $this->tryApi(function () use ($phuongThucXetTuyen) {
            return ApiResponse::success($phuongThucXetTuyen, 'Lấy chi tiết phương thức xét tuyển thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ma_phuong_thuc' => ['required', 'string', 'max:50', 'unique:danh_muc_phuong_thuc_xet_tuyen,ma_phuong_thuc'],
                'ten_phuong_thuc' => ['required', 'string', 'max:255'],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $item = PhuongThucXetTuyen::create($validated);

            return ApiResponse::success($item, 'Tạo phương thức xét tuyển thành công.');
        });
    }

    public function update(Request $request, PhuongThucXetTuyen $phuongThucXetTuyen): JsonResponse
    {
        return $this->tryApi(function () use ($request, $phuongThucXetTuyen) {
            $validated = $request->validate([
                'ma_phuong_thuc' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_phuong_thuc_xet_tuyen', 'ma_phuong_thuc')->ignore($phuongThucXetTuyen->id),
                ],
                'ten_phuong_thuc' => ['sometimes', 'string', 'max:255'],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $phuongThucXetTuyen->update($validated);

            return ApiResponse::success($phuongThucXetTuyen->fresh(), 'Cập nhật phương thức xét tuyển thành công.');
        });
    }

    public function destroy(PhuongThucXetTuyen $phuongThucXetTuyen): JsonResponse
    {
        return $this->tryApi(function () use ($phuongThucXetTuyen) {
            $phuongThucXetTuyen->delete();

            return ApiResponse::success(null, 'Đã xóa phương thức xét tuyển.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_phuong_thuc_xet_tuyen,id'],
            ]);

            $count = PhuongThucXetTuyen::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} phương thức xét tuyển.",
            );
        });
    }
}
