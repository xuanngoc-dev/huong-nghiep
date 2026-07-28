<?php

namespace App\Models;

use App\Enums\TrangThaiKhuVuc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ten_khu_vuc', 'ma_khu_vuc', 'ghi_chu', 'trang_thai'])]
class KhuVuc extends Model
{
    protected $table = 'danh_muc_khu_vuc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiKhuVuc::class,
        ];
    }

    public function tinhThanhs(): HasMany
    {
        return $this->hasMany(TinhThanh::class, 'khu_vuc_id');
    }
}
