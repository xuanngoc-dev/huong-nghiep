<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cau_hoi_id',
    'cau_tra_loi_id',
    'nguoi_dung_id',
    'nganh_hoc_id',
    'nhom_nganh_id',
    'diem_so',
    'ssid',
    'ma_loai_cau_hoi',
])]
class TracNghiemLichSuTraLoi extends Model
{
    protected $table = 'trac_nghiem_lich_su_tra_loi_chi_tiet';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diem_so' => 'float',
        ];
    }

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(TracNghiemCauHoi::class, 'cau_hoi_id');
    }

    public function cauTraLoi(): BelongsTo
    {
        return $this->belongsTo(TracNghiemCauTraLoi::class, 'cau_tra_loi_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }

    public function nganhHoc(): BelongsTo
    {
        return $this->belongsTo(NganhHoc::class, 'nganh_hoc_id');
    }

    public function nhomNganh(): BelongsTo
    {
        return $this->belongsTo(NhomNganh::class, 'nhom_nganh_id');
    }
}
