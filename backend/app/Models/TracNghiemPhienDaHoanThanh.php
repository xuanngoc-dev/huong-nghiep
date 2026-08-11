<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ssid',
    'nganh_hoc',
    'nhom_nganh',
    'nguoi_khao_sat_id',
])]
class TracNghiemPhienDaHoanThanh extends Model
{
    protected $table = 'trac_nghiem_phien_trac_nghiem_da_hoan_thanh';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nganh_hoc' => 'array',
            'nhom_nganh' => 'array',
        ];
    }

    public function nguoiKhaoSat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_khao_sat_id');
    }
}
