<?php

namespace App\Models;

use App\Enums\TrangThaiNganhHoc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ten_nganh', 'ma_nganh', 'ghi_chu', 'nhom_nganh_ids', 'trang_thai'])]
class NganhHoc extends Model
{
    protected $table = 'danh_muc_nganh_hoc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nhom_nganh_ids' => 'array',
            'trang_thai' => TrangThaiNganhHoc::class,
        ];
    }

    public function chuyenNganhs(): HasMany
    {
        return $this->hasMany(ChuyenNganh::class, 'nganh_hoc_id');
    }
}
