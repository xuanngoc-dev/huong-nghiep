<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NguoiDungController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $gioiTinh = trim((string) $request->query('gioi_tinh', ''));

            $query = NguoiDung::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ho_ten', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
                    });
                })
                ->when($gioiTinh !== '', fn ($query) => $query->where('gioi_tinh', $gioiTinh))
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (NguoiDung $item) => $this->toPublicArray($item))->values(),
                'Lấy danh sách người dùng thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(NguoiDung $nguoiDung): JsonResponse
    {
        return $this->tryApi(function () use ($nguoiDung) {
            return ApiResponse::success(
                $this->toPublicArray($nguoiDung),
                'Lấy chi tiết người dùng thành công.',
            );
        });
    }

    public function destroy(NguoiDung $nguoiDung): JsonResponse
    {
        return $this->tryApi(function () use ($nguoiDung) {
            $nguoiDung->delete();

            return ApiResponse::success(null, 'Đã xóa người dùng.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:nguoi_dung,id'],
            ]);

            $count = NguoiDung::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} người dùng.",
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(NguoiDung $nguoiDung): array
    {
        return [
            'id' => $nguoiDung->id,
            'ho_ten' => $nguoiDung->ho_ten,
            'email' => $nguoiDung->email,
            'ngay_sinh' => $nguoiDung->ngay_sinh?->format('Y-m-d'),
            'gioi_tinh' => $nguoiDung->gioi_tinh,
            'so_dien_thoai' => $nguoiDung->so_dien_thoai,
            'dan_toc' => $nguoiDung->dan_toc,
            'ton_giao' => $nguoiDung->ton_giao,
            'trinh_do_hoc_van' => $nguoiDung->trinh_do_hoc_van,
            'suc_khoe_the_chat' => $nguoiDung->suc_khoe_the_chat,
            'kha_nang_tai_chinh' => $nguoiDung->kha_nang_tai_chinh,
            'vi_tri_dia_ly' => $nguoiDung->vi_tri_dia_ly,
            'created_at' => $nguoiDung->created_at?->toIso8601String(),
            'updated_at' => $nguoiDung->updated_at?->toIso8601String(),
        ];
    }
}
