<?php

namespace App\Services;

use App\Enums\LoaiNapTien;
use App\Enums\TrangThaiNapEduCoin;
use App\Enums\TrangThaiYeuCauNapEduCoin;
use App\Models\LichSuNapEduCoin;
use App\Models\NapEduCoin;
use App\Models\NguoiDung;
use App\Support\MaGiaoDich;

class DuyetYeuCauNapEduCoin
{
    public function duyetTheoMaVaSoTien(string $maGiaoDich, int $soTienNap, ?int $nguoiDuyetId = null): ?NapEduCoin
    {
        $maGiaoDich = MaGiaoDich::normalize($maGiaoDich);
        if ($maGiaoDich === '' || $soTienNap < 0) {
            return null;
        }

        $item = NapEduCoin::query()
            ->where('ma_giao_dich', $maGiaoDich)
            ->where('so_tien_nap', $soTienNap)
            ->where('trang_thai', TrangThaiYeuCauNapEduCoin::ChoDuyet)
            ->lockForUpdate()
            ->first();

        if ($item === null) {
            return null;
        }

        return $this->duyet($item, $nguoiDuyetId);
    }

    public function duyet(NapEduCoin $item, ?int $nguoiDuyetId = null): ?NapEduCoin
    {
        if ($item->trang_thai !== TrangThaiYeuCauNapEduCoin::ChoDuyet) {
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
        }

        $soLuong = (int) $item->so_luong_edu_coin;
        $soDuTruocNap = (int) $profile->edu_coin;
        $soDuSauNap = $soDuTruocNap + $soLuong;

        $profile->edu_coin = $soDuSauNap;
        $profile->save();

        $item->nguoi_duyet_id = $nguoiDuyetId;
        $item->trang_thai = TrangThaiYeuCauNapEduCoin::DaDuyet;
        $item->save();

        $thongTinThanhToan = is_array($item->thong_tin_thanh_toan)
            ? $item->thong_tin_thanh_toan
            : [];
        $thongTinThanhToan['yeu_cau_nap_id'] = $item->id;
        $maGiaoDich = is_string($item->ma_giao_dich) && $item->ma_giao_dich !== ''
            ? $item->ma_giao_dich
            : MaGiaoDich::taoMaNap();
        $thongTinThanhToan['ma_giao_dich'] = $maGiaoDich;
        $thongTinThanhToan['noi_dung_chuyen_khoan'] = $maGiaoDich;

        LichSuNapEduCoin::query()->create([
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

        return $item;
    }
}
