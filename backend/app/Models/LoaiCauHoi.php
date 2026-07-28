<?php

namespace App\Models;

use App\Enums\TrangThaiLoaiCauHoi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_loai_cau_hoi', 'ma_loai_cau_hoi', 'ghi_chu', 'trang_thai'])]
class LoaiCauHoi extends Model
{
    protected $table = 'danh_muc_loai_cau_hoi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiLoaiCauHoi::class,
        ];
    }
}
