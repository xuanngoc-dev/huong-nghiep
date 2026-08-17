<?php

namespace App\Enums;

enum LoaiGiaoDich: string
{
    case AdminNap = 'admin_nap';
    case NguoiDungNap = 'nguoi_dung_nap';
    case ThanhToanTracNghiem = 'thanh_toan_trac_nghiem';

    public function label(): string
    {
        return match ($this) {
            self::AdminNap => 'Admin nạp cho người dùng',
            self::NguoiDungNap => 'Người dùng tự nạp',
            self::ThanhToanTracNghiem => 'Thanh toán trắc nghiệm',
        };
    }

    public function isTangCoin(): bool
    {
        return match ($this) {
            self::AdminNap, self::NguoiDungNap => true,
            self::ThanhToanTracNghiem => false,
        };
    }
}
