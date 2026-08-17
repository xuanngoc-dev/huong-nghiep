<?php

namespace App\Http\Controllers\Api;

use App\Enums\KenhThanhToan;
use App\Enums\LoaiGiaoDich;
use App\Enums\LoaiKhuyenMai;
use App\Enums\TrangThaiNapEduCoin;
use App\Http\Controllers\Controller;
use App\Models\LichSuNapEduCoin;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LichSuNapEduCoinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem lịch sử biến động.');
            }

            $start = max(0, (int) $request->query('start', 0));
            $limit = (int) $request->query('limit', 10);
            $limit = max(1, min($limit, 100));

            $query = LichSuNapEduCoin::query()
                ->where('nguoi_nap_id', $user->id)
                ->orderByDesc('id');

            $total = (clone $query)->count();
            $items = (clone $query)->skip($start)->take($limit)->get();

            return ApiResponse::success(
                $items->map(fn (LichSuNapEduCoin $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử biến động Edu Coin thành công.',
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
    private function toPublicArray(LichSuNapEduCoin $item): array
    {
        $loaiGiaoDich = $item->loai_giao_dich instanceof LoaiGiaoDich
            ? $item->loai_giao_dich
            : LoaiGiaoDich::tryFrom((string) $item->loai_giao_dich);

        $kenh = $item->kenh_thanh_toan instanceof KenhThanhToan
            ? $item->kenh_thanh_toan
            : KenhThanhToan::tryFrom((string) $item->kenh_thanh_toan);

        $trangThai = $item->trang_thai instanceof TrangThaiNapEduCoin
            ? $item->trang_thai
            : TrangThaiNapEduCoin::tryFrom((string) $item->trang_thai);

        $loaiKhuyenMai = $item->loai_khuyen_mai instanceof LoaiKhuyenMai
            ? $item->loai_khuyen_mai
            : LoaiKhuyenMai::tryFrom((string) $item->loai_khuyen_mai);

        $thongTin = is_array($item->thong_tin_thanh_toan) ? $item->thong_tin_thanh_toan : [];

        return [
            'id' => $item->id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'loai_giao_dich' => $loaiGiaoDich?->value,
            'loai_giao_dich_label' => $loaiGiaoDich?->label(),
            'so_du_truoc_gd' => (int) $item->so_du_truoc_gd,
            'so_du_sau_gd' => (int) $item->so_du_sau_gd,
            'so_coin_gd' => (int) $item->so_coin_gd,
            'so_tien_thanh_toan' => (int) $item->so_tien_thanh_toan,
            'loai_khuyen_mai' => $loaiKhuyenMai?->value,
            'coin_khuyen_mai' => (int) $item->coin_khuyen_mai,
            'tong_coin_nhan' => (int) $item->tong_coin_nhan,
            'kenh_thanh_toan' => $kenh?->value,
            'kenh_thanh_toan_label' => $kenh?->label(),
            'ten_ngan_hang' => $thongTin['ten_ngan_hang'] ?? null,
            'ghi_chu' => $item->ghi_chu,
            'trang_thai' => $trangThai?->value ?? TrangThaiNapEduCoin::DangXuLy->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiNapEduCoin::DangXuLy->label(),
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
