<?php

namespace App\Models;

use App\Enums\TrangThaiLoaiCauHoi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ten_loai_cau_hoi', 'ma_loai_cau_hoi', 'ghi_chu', 'thu_tu_uu_tien', 'trang_thai'])]
class LoaiCauHoi extends Model
{
    protected $table = 'danh_muc_loai_cau_hoi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'thu_tu_uu_tien' => 'integer',
            'trang_thai' => TrangThaiLoaiCauHoi::class,
        ];
    }

    public function cauHois(): HasMany
    {
        return $this->hasMany(TracNghiemCauHoi::class, 'loai_cau_hoi_id');
    }
}
