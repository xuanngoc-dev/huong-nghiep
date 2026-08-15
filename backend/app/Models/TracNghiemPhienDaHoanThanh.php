<?php

namespace App\Models;

use App\Enums\TrangThaiLichSuPhien;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'ssid',
    'trang_thai',
    'nhom_nganh',
    'thong_tin_thanh_toan',
    'chi_tiet_ket_qua',
    'nguoi_khao_sat_id',
])]
class TracNghiemPhienDaHoanThanh extends Model
{
    protected $table = 'trac_nghiem_lich_su_phien';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiLichSuPhien::class,
            'nhom_nganh' => 'array',
            'thong_tin_thanh_toan' => 'array',
            'chi_tiet_ket_qua' => 'array',
        ];
    }

    public function nguoiKhaoSat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_khao_sat_id');
    }

    public function thanhToans(): HasMany
    {
        return $this->hasMany(TracNghiemLichSuThanhToan::class, 'lich_su_phien_id');
    }
}
