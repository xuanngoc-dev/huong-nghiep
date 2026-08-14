<?php

namespace App\Enums;

enum HinhThucNhanXu: string
{
    case DiemDanh = 'diem_danh';

    public function label(): string
    {
        return match ($this) {
            self::DiemDanh => 'Điểm danh',
        };
    }
}
