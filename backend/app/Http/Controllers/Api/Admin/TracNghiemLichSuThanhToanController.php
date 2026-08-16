<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\HinhThucThanhToanTracNghiem;
use App\Enums\TrangThaiThanhToanTracNghiem;
use App\Http\Controllers\Controller;
use App\Models\TracNghiemLichSuThanhToan;
use App\Models\TracNghiemPhienDaHoanThanh;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TracNghiemLichSuThanhToanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));
            $hinhThuc = trim((string) $request->query('hinh_thuc_thanh_toan', ''));

            $query = TracNghiemLichSuThanhToan::query()
                ->with([
                    'nguoiDung:id,name,email,so_dien_thoai',
                    'lichSuPhien:id,ssid,nguoi_khao_sat_id',
                ])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereHas('nguoiDung', function ($userQuery) use ($keyword) {
                            $userQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
                        })->orWhereHas('lichSuPhien', function ($phienQuery) use ($keyword) {
                            $phienQuery->where('ssid', 'like', "%{$keyword}%");
                        })->orWhere('ma_giao_dich', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiThanhToanTracNghiem::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when(
                    $hinhThuc !== '' && HinhThucThanhToanTracNghiem::tryFrom($hinhThuc) !== null,
                    fn ($query) => $query->where('hinh_thuc_thanh_toan', $hinhThuc),
                )
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (TracNghiemLichSuThanhToan $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử thanh toán trắc nghiệm thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(TracNghiemLichSuThanhToan $thanhToan): JsonResponse
    {
        return $this->tryApi(function () use ($thanhToan) {
            $thanhToan->load([
                'nguoiDung:id,name,email,so_dien_thoai',
                'lichSuPhien:id,ssid,nguoi_khao_sat_id',
            ]);

            return ApiResponse::success(
                $this->toPublicArray($thanhToan),
                'Lấy chi tiết thanh toán trắc nghiệm thành công.',
            );
        });
    }

    public function duyet(TracNghiemLichSuThanhToan $thanhToan): JsonResponse
    {
        return $this->tryApi(function () use ($thanhToan) {
            $updated = DB::transaction(function () use ($thanhToan) {
                /** @var TracNghiemLichSuThanhToan $item */
                $item = TracNghiemLichSuThanhToan::query()->lockForUpdate()->findOrFail($thanhToan->id);
                if ($item->trang_thai !== TrangThaiThanhToanTracNghiem::DangXuLy) {
                    return null;
                }

                $phien = TracNghiemPhienDaHoanThanh::query()
                    ->lockForUpdate()
                    ->find($item->lich_su_phien_id);

                if ($phien === null) {
                    return ['error' => 'Không tìm thấy phiên trắc nghiệm.'];
                }

                $daThanhToan = ! empty($phien->thong_tin_thanh_toan)
                    || TracNghiemLichSuThanhToan::query()
                        ->where('lich_su_phien_id', $phien->id)
                        ->where('id', '!=', $item->id)
                        ->where('trang_thai', TrangThaiThanhToanTracNghiem::DaHoanThanh)
                        ->exists();

                if ($daThanhToan) {
                    return ['error' => 'Phiên trắc nghiệm này đã được thanh toán.'];
                }

                $item->trang_thai = TrangThaiThanhToanTracNghiem::DaHoanThanh;
                $item->save();

                $info = is_array($item->thong_tin_thanh_toan) ? $item->thong_tin_thanh_toan : [];
                $hinhThuc = $item->hinh_thuc_thanh_toan instanceof HinhThucThanhToanTracNghiem
                    ? $item->hinh_thuc_thanh_toan->value
                    : (string) $item->hinh_thuc_thanh_toan;

                $phien->thong_tin_thanh_toan = [
                    ...$info,
                    'thanh_toan_id' => $item->id,
                    'hinh_thuc_thanh_toan' => $hinhThuc,
                    'so_tien_thanh_toan' => (int) $item->so_tien_thanh_toan,
                    'thanh_toan_luc' => now()->toIso8601String(),
                ];
                $phien->save();

                return $item;
            });

            if ($updated === null) {
                return ApiResponse::error('Yêu cầu thanh toán không còn ở trạng thái đang xử lý.');
            }

            if (is_array($updated) && isset($updated['error'])) {
                return ApiResponse::error($updated['error']);
            }

            $updated->load([
                'nguoiDung:id,name,email,so_dien_thoai',
                'lichSuPhien:id,ssid,nguoi_khao_sat_id',
            ]);

            return ApiResponse::success(
                $this->toPublicArray($updated),
                'Đã duyệt thanh toán trắc nghiệm.',
            );
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toUserArray(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(TracNghiemLichSuThanhToan $item): array
    {
        $hinhThuc = $item->hinh_thuc_thanh_toan instanceof HinhThucThanhToanTracNghiem
            ? $item->hinh_thuc_thanh_toan
            : HinhThucThanhToanTracNghiem::tryFrom((string) $item->hinh_thuc_thanh_toan);

        $trangThai = $item->trang_thai instanceof TrangThaiThanhToanTracNghiem
            ? $item->trang_thai
            : TrangThaiThanhToanTracNghiem::tryFrom((string) $item->trang_thai);

        $thongTin = is_array($item->thong_tin_thanh_toan) ? $item->thong_tin_thanh_toan : [];

        return [
            'id' => $item->id,
            'lich_su_phien_id' => $item->lich_su_phien_id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'ssid' => $item->lichSuPhien?->ssid,
            'nguoi_dung_id' => $item->nguoi_dung_id,
            'nguoi_dung' => $this->toUserArray($item->nguoiDung),
            'hinh_thuc_thanh_toan' => $hinhThuc?->value,
            'hinh_thuc_thanh_toan_label' => $hinhThuc?->label(),
            'so_tien_thanh_toan' => (int) $item->so_tien_thanh_toan,
            'trang_thai' => $trangThai?->value ?? TrangThaiThanhToanTracNghiem::DangXuLy->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiThanhToanTracNghiem::DangXuLy->label(),
            'thong_tin_thanh_toan' => $thongTin,
            'ten_ngan_hang' => $thongTin['ten_ngan_hang'] ?? null,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
