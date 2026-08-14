<?php

namespace App\Http\Controllers\Api;

use App\Enums\HinhThucNhanXu;
use App\Enums\TrangThaiNhanXu;
use App\Http\Controllers\Controller;
use App\Models\LichSuNhanXu;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LichSuNhanXuController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem lịch sử nhận xu.');
            }

            $start = max(0, (int) $request->query('start', 0));
            $limit = (int) $request->query('limit', 10);
            $limit = max(1, min($limit, 100));

            $query = LichSuNhanXu::query()
                ->where('nguoi_dung_id', $user->id)
                ->orderByDesc('id');

            $total = (clone $query)->count();
            $items = (clone $query)->skip($start)->take($limit)->get();

            return ApiResponse::success(
                $items->map(fn (LichSuNhanXu $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử nhận xu hệ thống thành công.',
                [
                    'total' => $total,
                    'start' => $start,
                    'limit' => $limit,
                ],
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(LichSuNhanXu $item): array
    {
        $hinhThuc = $item->hinh_thuc_nhan_xu instanceof HinhThucNhanXu
            ? $item->hinh_thuc_nhan_xu
            : HinhThucNhanXu::tryFrom((string) $item->hinh_thuc_nhan_xu);

        $trangThai = $item->trang_thai instanceof TrangThaiNhanXu
            ? $item->trang_thai
            : TrangThaiNhanXu::tryFrom((string) $item->trang_thai);

        return [
            'id' => $item->id,
            'hinh_thuc_nhan_xu' => $hinhThuc?->value,
            'hinh_thuc_nhan_xu_label' => $hinhThuc?->label(),
            'ngay_nhan' => $item->ngay_nhan?->toDateString(),
            'so_du_truoc_khi_nhan' => (int) $item->so_du_truoc_khi_nhan,
            'so_xu_nhan_duoc' => (int) $item->so_xu_nhan_duoc,
            'so_du_sau_khi_nhan' => (int) $item->so_du_sau_khi_nhan,
            'trang_thai' => $trangThai?->value ?? TrangThaiNhanXu::ThanhCong->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiNhanXu::ThanhCong->label(),
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
