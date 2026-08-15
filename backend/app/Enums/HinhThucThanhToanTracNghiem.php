<?php

namespace App\Enums;

enum HinhThucThanhToanTracNghiem: string
{
    case ChuyenKhoan = 'chuyen_khoan';
    case EduCoin = 'edu_coin';

    public function label(): string
    {
        return match ($this) {
            self::ChuyenKhoan => 'Chuyển khoản',
            self::EduCoin => 'Edu Coin',
        };
    }
}
