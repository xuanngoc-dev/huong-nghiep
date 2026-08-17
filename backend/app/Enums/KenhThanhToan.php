<?php

namespace App\Enums;

enum KenhThanhToan: string
{
    case TienMat = 'tien_mat';
    case ChuyenKhoan = 'chuyen_khoan';
    case EduCoin = 'edu_coin';

    public function label(): string
    {
        return match ($this) {
            self::TienMat => 'Tiền mặt',
            self::ChuyenKhoan => 'Chuyển khoản',
            self::EduCoin => 'Edu Coin',
        };
    }
}
