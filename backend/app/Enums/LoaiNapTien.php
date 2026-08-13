<?php

namespace App\Enums;

enum LoaiNapTien: string
{
    case AdminNap = 'admin_nap';
    case NguoiDungNap = 'nguoi_dung_nap';

    public function label(): string
    {
        return match ($this) {
            self::AdminNap => 'Admin nạp cho người dùng',
            self::NguoiDungNap => 'Người dùng tự nạp',
        };
    }
}
