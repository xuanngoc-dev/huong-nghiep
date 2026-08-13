<?php

namespace App\Enums;

enum TrangThaiNapEduCoin: string
{
    case DangXuLy = 'dang_xu_ly';
    case DaDuyet = 'da_duyet';
    case DaHuy = 'da_huy';

    public function label(): string
    {
        return match ($this) {
            self::DangXuLy => 'Đang xử lý',
            self::DaDuyet => 'Đã duyệt',
            self::DaHuy => 'Đã hủy',
        };
    }
}
