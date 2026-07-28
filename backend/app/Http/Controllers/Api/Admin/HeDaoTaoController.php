<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeDaoTao;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HeDaoTaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = HeDaoTao::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_he_dao_tao', 'like', "%{$keyword}%")
                            ->orWhere('ma_he_dao_tao', 'like', "%{$keyword}%");
                    });
                })
                ->orderBy('ten_he_dao_tao');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách hệ đào tạo thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(HeDaoTao $heDaoTao): JsonResponse
    {
        return $this->tryApi(function () use ($heDaoTao) {
            return ApiResponse::success($heDaoTao, 'Lấy chi tiết hệ đào tạo thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_he_dao_tao' => ['required', 'string', 'max:255'],
                'ma_he_dao_tao' => ['required', 'string', 'max:50', 'unique:danh_muc_he_dao_tao,ma_he_dao_tao'],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $item = HeDaoTao::create($validated);

            return ApiResponse::success($item, 'Tạo hệ đào tạo thành công.');
        });
    }

    public function update(Request $request, HeDaoTao $heDaoTao): JsonResponse
    {
        return $this->tryApi(function () use ($request, $heDaoTao) {
            $validated = $request->validate([
                'ten_he_dao_tao' => ['sometimes', 'string', 'max:255'],
                'ma_he_dao_tao' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_he_dao_tao', 'ma_he_dao_tao')->ignore($heDaoTao->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
            ]);

            $heDaoTao->update($validated);

            return ApiResponse::success($heDaoTao->fresh(), 'Cập nhật hệ đào tạo thành công.');
        });
    }

    public function destroy(HeDaoTao $heDaoTao): JsonResponse
    {
        return $this->tryApi(function () use ($heDaoTao) {
            $heDaoTao->delete();

            return ApiResponse::success(null, 'Đã xóa hệ đào tạo.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_he_dao_tao,id'],
            ]);

            $count = HeDaoTao::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} hệ đào tạo.",
            );
        });
    }
}
