<?php

namespace App\Models;

use App\Enums\TrangThaiTracNghiemCauHoi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nhom_nganh_id',
    'loai_cau_hoi_id',
    'noi_dung_cau_hoi',
    'ghi_chu',
    'trang_thai',
])]
class TracNghiemCauHoi extends Model
{
    protected $table = 'trac_nghiem_cau_hoi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiTracNghiemCauHoi::class,
        ];
    }

    public function nhomNganh(): BelongsTo
    {
        return $this->belongsTo(NhomNganh::class, 'nhom_nganh_id');
    }

    public function loaiCauHoi(): BelongsTo
    {
        return $this->belongsTo(LoaiCauHoi::class, 'loai_cau_hoi_id');
    }

    public function cauTraLois(): HasMany
    {
        return $this->hasMany(TracNghiemCauTraLoi::class, 'cau_hoi_id');
    }
}
