<?php

namespace App\Http\Controllers\Api;

use App\Enums\HinhThucThanhToanTracNghiem;
use App\Enums\TrangThaiLichSuPhien;
use App\Enums\TrangThaiThanhToanTracNghiem;
use App\Http\Controllers\Controller;
use App\Models\TracNghiemLichSuThanhToan;
use App\Models\TracNghiemPhienDaHoanThanh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LichSuTracNghiemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem lịch sử trắc nghiệm.');
            }

            $query = TracNghiemPhienDaHoanThanh::query()
                ->where('nguoi_khao_sat_id', $user->id)
                ->with(['thanhToans' => fn ($q) => $q->orderByDesc('id')])
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (TracNghiemPhienDaHoanThanh $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử trắc nghiệm thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(Request $request, int $id): JsonResponse
    {
        return $this->tryApi(function () use ($request, $id) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem chi tiết phiên trắc nghiệm.');
            }

            $item = TracNghiemPhienDaHoanThanh::query()
                ->where('id', $id)
                ->where('nguoi_khao_sat_id', $user->id)
                ->with(['thanhToans' => fn ($q) => $q->orderByDesc('id')])
                ->first();

            if (! $item) {
                return ApiResponse::error('Không tìm thấy phiên trắc nghiệm.');
            }

            return ApiResponse::success(
                $this->toPublicArray($item, true),
                'Lấy chi tiết phiên trắc nghiệm thành công.',
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(TracNghiemPhienDaHoanThanh $item, bool $withDetail = false): array
    {
        $nhomNganh = is_array($item->nhom_nganh) ? $item->nhom_nganh : [];
        $topNhom = $nhomNganh[0] ?? null;
        $chiTiet = is_array($item->chi_tiet_ket_qua) ? $item->chi_tiet_ket_qua : [];
        $trangThai = $item->trang_thai instanceof TrangThaiLichSuPhien
            ? $item->trang_thai
            : TrangThaiLichSuPhien::tryFrom((string) $item->trang_thai);

        $thanhToans = $item->relationLoaded('thanhToans') ? $item->thanhToans : collect();
        $thanhToanHoanThanh = $thanhToans->first(
            fn (TracNghiemLichSuThanhToan $row) => $row->trang_thai === TrangThaiThanhToanTracNghiem::DaHoanThanh
                || $row->trang_thai === TrangThaiThanhToanTracNghiem::DaHoanThanh->value,
        );
        $thanhToanDangXuLy = $thanhToans->first(
            fn (TracNghiemLichSuThanhToan $row) => $row->trang_thai === TrangThaiThanhToanTracNghiem::DangXuLy
                || $row->trang_thai === TrangThaiThanhToanTracNghiem::DangXuLy->value,
        );
        $daThanhToan = $thanhToanHoanThanh !== null || ! empty($item->thong_tin_thanh_toan);

        $payload = [
            'id' => $item->id,
            'ssid' => $item->ssid,
            'ma_giao_dich' => $item->ma_giao_dich,
            'trang_thai' => $trangThai?->value ?? TrangThaiLichSuPhien::ChuaHoanThanh->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiLichSuPhien::ChuaHoanThanh->label(),
            'top_nhom_nganh' => $topNhom,
            'so_nhom_nganh' => count($nhomNganh),
            'tong_diem' => $chiTiet['tong_diem'] ?? ($topNhom['tong_diem'] ?? null),
            'so_cau_da_tra_loi' => $chiTiet['so_cau_da_tra_loi'] ?? null,
            'da_thanh_toan' => $daThanhToan,
            'thanh_toan_dang_xu_ly' => $daThanhToan ? null : $this->toThanhToanArray($thanhToanDangXuLy),
            'created_at' => $item->created_at?->toIso8601String(),
        ];

        if ($withDetail) {
            $payload['nhom_nganh'] = $nhomNganh;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toThanhToanArray(?TracNghiemLichSuThanhToan $item): ?array
    {
        if ($item === null) {
            return null;
        }

        $hinhThuc = $item->hinh_thuc_thanh_toan instanceof HinhThucThanhToanTracNghiem
            ? $item->hinh_thuc_thanh_toan
            : HinhThucThanhToanTracNghiem::tryFrom((string) $item->hinh_thuc_thanh_toan);

        $trangThai = $item->trang_thai instanceof TrangThaiThanhToanTracNghiem
            ? $item->trang_thai
            : TrangThaiThanhToanTracNghiem::tryFrom((string) $item->trang_thai);

        return [
            'id' => $item->id,
            'lich_su_phien_id' => $item->lich_su_phien_id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'hinh_thuc_thanh_toan' => $hinhThuc?->value,
            'hinh_thuc_thanh_toan_label' => $hinhThuc?->label(),
            'so_tien_thanh_toan' => (int) $item->so_tien_thanh_toan,
            'trang_thai' => $trangThai?->value ?? TrangThaiThanhToanTracNghiem::DangXuLy->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiThanhToanTracNghiem::DangXuLy->label(),
            'thong_tin_thanh_toan' => $item->thong_tin_thanh_toan,
        ];
    }
}
