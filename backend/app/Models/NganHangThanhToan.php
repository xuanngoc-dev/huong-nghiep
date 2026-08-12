<?php

namespace App\Models;

use App\Enums\TrangThaiNganHangThanhToan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'ten_ngan_hang',
    'ten_viet_tat',
    'hinh_anh_logo',
    'so_tai_khoan',
    'chu_tai_khoan',
    'chi_nhanh',
    'trang_thai',
    'ghi_chu',
])]
class NganHangThanhToan extends Model
{
    protected $table = 'he_thong_ngan_hang_thanh_toan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiNganHangThanhToan::class,
        ];
    }
}
