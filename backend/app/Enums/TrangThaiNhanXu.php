<?php

namespace App\Enums;

enum TrangThaiNhanXu: string
{
    case ThanhCong = 'thanh_cong';

    public function label(): string
    {
        return match ($this) {
            self::ThanhCong => 'Thành công',
        };
    }
}
