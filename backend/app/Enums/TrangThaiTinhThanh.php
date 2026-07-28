<?php

namespace App\Enums;

enum TrangThaiTinhThanh: string
{
    case DangSuDung = 'dang_su_dung';
    case NgungSuDung = 'ngung_su_dung';

    public function label(): string
    {
        return match ($this) {
            self::DangSuDung => 'Đang sử dụng',
            self::NgungSuDung => 'Ngừng sử dụng',
        };
    }
}
