<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'noi_dung',
    'so_tien',
    'thoi_gian',
    'mo_ta',
])]
class TracNghiemLichSuThanhToanEduCoin extends Model
{
    protected $table = 'trac_nghiem_lich_su_thanh_toan_edu_coin';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'so_tien' => 'integer',
            'thoi_gian' => 'datetime',
        ];
    }
}
