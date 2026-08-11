<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ma_truong',
    'nam_hoc',
    'nganh_hoc_tuyen_sinh_id',
    'chuyen_nganh_tuyen_sinh_id',
    'phuong_thuc_xet_tuyen',
    'to_hop_xet_tuyen',
    'chi_tieu',
    'diem_chuan',
    'ghi_chu',
])]
class TruongHocTuyenSinhTheoNam extends Model
{
    protected $table = 'truong_hoc_tuyen_sinh_theo_nam';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'chi_tieu' => 'integer',
            'diem_chuan' => 'float',
        ];
    }

    public function truongHoc(): BelongsTo
    {
        return $this->belongsTo(TruongHoc::class, 'ma_truong', 'ma_truong');
    }

    public function nganhHocTuyenSinh(): BelongsTo
    {
        return $this->belongsTo(NganhHoc::class, 'nganh_hoc_tuyen_sinh_id');
    }

    public function chuyenNganhTuyenSinh(): BelongsTo
    {
        return $this->belongsTo(ChuyenNganh::class, 'chuyen_nganh_tuyen_sinh_id');
    }
}
