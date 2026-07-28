<?php

namespace App\Models;

use App\Enums\TrangThaiNganhHoc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ma_chuyen_nganh',
    'ten_chuyen_nganh',
    'nganh_hoc_id',
    'mo_ta',
    'ghi_chu',
    'trang_thai',
])]
class ChuyenNganh extends Model
{
    protected $table = 'danh_muc_chuyen_nganh';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiNganhHoc::class,
        ];
    }

    public function nganhHoc(): BelongsTo
    {
        return $this->belongsTo(NganhHoc::class, 'nganh_hoc_id');
    }
}
