<?php

namespace App\Enums;

enum TrangThaiUser: string
{
    case DangHoatDong = 'dang_hoat_dong';
    case NgungHoatDong = 'ngung_hoat_dong';

    public function label(): string
    {
        return match ($this) {
            self::DangHoatDong => 'Đang hoạt động',
            self::NgungHoatDong => 'Ngừng hoạt động',
        };
    }
}
