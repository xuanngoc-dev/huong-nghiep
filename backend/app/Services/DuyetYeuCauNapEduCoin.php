<?php

namespace App\Services;

use App\Enums\LoaiNapTien;
use App\Enums\TrangThaiNapEduCoin;
use App\Enums\TrangThaiYeuCauNapEduCoin;
use App\Models\LichSuNapEduCoin;
use App\Models\NapEduCoin;
use App\Models\NguoiDung;
use App\Support\MaGiaoDich;
use Illuminate\Support\Facades\Log;

class DuyetYeuCauNapEduCoin
{
    public function duyetTheoMaVaSoTien(string $maGiaoDich, int $soTienNap, ?int $nguoiDuyetId = null): ?NapEduCoin
    {
        $maGiaoDich = MaGiaoDich::normalize($maGiaoDich);
        if ($maGiaoDich === '' || $soTienNap < 0) {
            $this->log('warning', 'Mã giao dịch rỗng hoặc số tiền không hợp lệ', [
                'ma_giao_dich' => $maGiaoDich,
                'so_tien_nap' => $soTienNap,
            ]);

            return null;
        }

        $item = NapEduCoin::query()
            ->where('ma_giao_dich', $maGiaoDich)
            ->where('so_tien_nap', $soTienNap)
            ->where('trang_thai', TrangThaiYeuCauNapEduCoin::ChoDuyet)
            ->lockForUpdate()
            ->first();

        if ($item === null) {
            $cungMa = NapEduCoin::query()
                ->where('ma_giao_dich', $maGiaoDich)
                ->get(['id', 'nguoi_nap_id', 'so_tien_nap', 'trang_thai']);

            $this->log('warning', 'Không tìm thấy yêu cầu nạp khớp mã + số tiền + chờ duyệt', [
                'ma_giao_dich' => $maGiaoDich,
                'so_tien_nap' => $soTienNap,
                'so_ban_ghi_cung_ma' => $cungMa->count(),
                'ban_ghi_cung_ma' => $cungMa->map(static fn (NapEduCoin $row): array => [
                    'id' => $row->id,
                    'nguoi_nap_id' => $row->nguoi_nap_id,
                    'so_tien_nap' => (int) $row->so_tien_nap,
                    'trang_thai' => $row->trang_thai?->value,
                    'khop_so_tien' => (int) $row->so_tien_nap === $soTienNap,
                ])->all(),
            ]);

            return null;
        }

        $this->log('info', 'Tìm thấy yêu cầu nạp chờ duyệt, tiến hành duyệt', [
            'ma_giao_dich' => $maGiaoDich,
            'nap_edu_coin_id' => $item->id,
            'nguoi_nap_id' => $item->nguoi_nap_id,
            'so_tien_nap' => (int) $item->so_tien_nap,
            'so_luong_edu_coin' => (int) $item->so_luong_edu_coin,
        ]);

        return $this->duyet($item, $nguoiDuyetId);
    }

    public function duyet(NapEduCoin $item, ?int $nguoiDuyetId = null): ?NapEduCoin
    {
        if ($item->trang_thai !== TrangThaiYeuCauNapEduCoin::ChoDuyet) {
            $this->log('warning', 'Bỏ qua duyệt vì yêu cầu không còn trạng thái chờ duyệt', [
                'nap_edu_coin_id' => $item->id,
                'ma_giao_dich' => $item->ma_giao_dich,
                'trang_thai' => $item->trang_thai?->value,
            ]);

            return null;
        }

        $profile = NguoiDung::query()
            ->where('user_id', $item->nguoi_nap_id)
            ->lockForUpdate()
            ->first();

        if ($profile === null) {
            $profile = NguoiDung::query()->create([
                'user_id' => $item->nguoi_nap_id,
                'edu_coin' => 0,
                'xu_he_thong' => 0,
            ]);

            $this->log('info', 'Tạo mới hồ sơ người dùng khi duyệt nạp', [
                'nap_edu_coin_id' => $item->id,
                'nguoi_nap_id' => $item->nguoi_nap_id,
            ]);
        }

        $soLuong = (int) $item->so_luong_edu_coin;
        $soDuTruocNap = (int) $profile->edu_coin;
        $soDuSauNap = $soDuTruocNap + $soLuong;

        $profile->edu_coin = $soDuSauNap;
        $profile->save();

        $item->nguoi_duyet_id = $nguoiDuyetId;
        $item->trang_thai = TrangThaiYeuCauNapEduCoin::DaDuyet;
        $item->save();

        $this->log('info', 'Đã cộng EduCoin và cập nhật trạng thái yêu cầu nạp', [
            'nap_edu_coin_id' => $item->id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'nguoi_nap_id' => $item->nguoi_nap_id,
            'so_du_truoc_nap' => $soDuTruocNap,
            'so_du_sau_nap' => $soDuSauNap,
            'so_coin_nap' => $soLuong,
        ]);

        $thongTinThanhToan = is_array($item->thong_tin_thanh_toan)
            ? $item->thong_tin_thanh_toan
            : [];
        $thongTinThanhToan['yeu_cau_nap_id'] = $item->id;
        $maGiaoDich = is_string($item->ma_giao_dich) && $item->ma_giao_dich !== ''
            ? $item->ma_giao_dich
            : MaGiaoDich::taoMaNap();
        $thongTinThanhToan['ma_giao_dich'] = $maGiaoDich;
        $thongTinThanhToan['noi_dung_chuyen_khoan'] = $maGiaoDich;

        $lichSu = LichSuNapEduCoin::query()->create([
            'nguoi_nap_id' => $item->nguoi_nap_id,
            'nguoi_duyet_id' => $nguoiDuyetId,
            'nguoi_tao_id' => $item->nguoi_nap_id,
            'ma_giao_dich' => $maGiaoDich,
            'loai_nap_tien' => LoaiNapTien::NguoiDungNap,
            'so_du_truoc_nap' => $soDuTruocNap,
            'so_du_sau_nap' => $soDuSauNap,
            'so_coin_nap' => $soLuong,
            'so_tien_thanh_toan' => (int) $item->so_tien_nap,
            'loai_khuyen_mai' => null,
            'coin_khuyen_mai' => 0,
            'tong_coin_nhan' => $soLuong,
            'kenh_thanh_toan' => $item->kenh_thanh_toan,
            'thong_tin_thanh_toan' => $thongTinThanhToan,
            'ghi_chu' => $item->ghi_chu,
            'trang_thai' => TrangThaiNapEduCoin::DaDuyet,
        ]);

        $this->log('info', 'Đã ghi lịch sử nạp EduCoin', [
            'nap_edu_coin_id' => $item->id,
            'lich_su_nap_id' => $lichSu->id,
            'ma_giao_dich' => $maGiaoDich,
        ]);

        return $item;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        Log::channel('sao_ke_ngan_hang')->log($level, '[duyet-nap-edu-coin] '.$message, $context);
    }
}
