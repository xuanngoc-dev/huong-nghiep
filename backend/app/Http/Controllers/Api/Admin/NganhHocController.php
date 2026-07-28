<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiNganhHoc;
use App\Http\Controllers\Controller;
use App\Models\NganhHoc;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NganhHocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = NganhHoc::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_nganh', 'like', "%{$keyword}%")
                            ->orWhere('ma_nganh', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_nganh');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách ngành học thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($nganhHoc) {
            return ApiResponse::success($nganhHoc, 'Lấy chi tiết ngành học thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_nganh' => ['required', 'string', 'max:255'],
                'ma_nganh' => ['required', 'string', 'max:50', 'unique:nganh_hoc,ma_nganh'],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $item = NganhHoc::create($validated);

            return ApiResponse::success($item, 'Tạo ngành học thành công.');
        });
    }

    public function update(Request $request, NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $nganhHoc) {
            $validated = $request->validate([
                'ten_nganh' => ['sometimes', 'string', 'max:255'],
                'ma_nganh' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('nganh_hoc', 'ma_nganh')->ignore($nganhHoc->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiNganhHoc::class)],
            ]);

            $nganhHoc->update($validated);

            return ApiResponse::success($nganhHoc->fresh(), 'Cập nhật ngành học thành công.');
        });
    }

    public function destroy(NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($nganhHoc) {
            $nganhHoc->delete();

            return ApiResponse::success(null, 'Đã xóa ngành học.');
        });
    }
}
