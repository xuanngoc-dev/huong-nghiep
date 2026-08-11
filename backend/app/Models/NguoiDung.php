<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'ho_ten',
    'ngay_sinh',
    'gioi_tinh',
    'email',
    'so_dien_thoai',
    'mat_khau',
    'dan_toc',
    'ton_giao',
    'trinh_do_hoc_van',
    'suc_khoe_the_chat',
    'kha_nang_tai_chinh',
    'vi_tri_dia_ly',
])]
#[Hidden(['mat_khau'])]
class NguoiDung extends Model
{
    protected $table = 'nguoi_dung';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ngay_sinh' => 'date:Y-m-d',
            'mat_khau' => 'hashed',
            'trinh_do_hoc_van' => 'array',
            'suc_khoe_the_chat' => 'array',
            'kha_nang_tai_chinh' => 'array',
            'vi_tri_dia_ly' => 'array',
        ];
    }
}
