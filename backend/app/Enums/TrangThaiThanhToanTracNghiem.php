<?php

namespace App\Enums;

enum TrangThaiThanhToanTracNghiem: string
{
    case DangXuLy = 'dang_xu_ly';
    case DaHoanThanh = 'da_hoan_thanh';

    public function label(): string
    {
        return match ($this) {
            self::DangXuLy => 'Đang xử lý',
            self::DaHoanThanh => 'Đã hoàn thành',
        };
    }
}
