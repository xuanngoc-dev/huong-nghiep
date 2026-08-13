<?php

namespace App\Enums;

enum LoaiKhuyenMai: string
{
    case PhanTram = 'phan_tram';
    case GiaTri = 'gia_tri';

    public function label(): string
    {
        return match ($this) {
            self::PhanTram => 'Theo %',
            self::GiaTri => 'Theo giá trị',
        };
    }
}
