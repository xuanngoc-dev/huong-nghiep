<?php

namespace App\Enums;

enum TrangThaiYeuCauNapEduCoin: string
{
    case ChoDuyet = 'cho_duyet';
    case DaDuyet = 'da_duyet';
    case HuyDuyet = 'huy_duyet';

    public function label(): string
    {
        return match ($this) {
            self::ChoDuyet => 'Chờ duyệt',
            self::DaDuyet => 'Đã duyệt',
            self::HuyDuyet => 'Hủy duyệt',
        };
    }
}
