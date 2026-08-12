<?php

namespace App\Enums;

enum TrangThaiLichSuPhien: string
{
    case HoanThanh = 'hoan_thanh';
    case ChuaHoanThanh = 'chua_hoan_thanh';

    public function label(): string
    {
        return match ($this) {
            self::HoanThanh => 'Hoàn thành',
            self::ChuaHoanThanh => 'Chưa hoàn thành',
        };
    }
}
