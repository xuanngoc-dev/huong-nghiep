<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'ngay_sinh',
    'gioi_tinh',
    'dan_toc',
    'ton_giao',
    'trinh_do_hoc_van',
    'suc_khoe_the_chat',
    'kha_nang_tai_chinh',
    'vi_tri_dia_ly',
    'edu_coin',
    'xu_he_thong',
])]
class NguoiDung extends Model
{
    protected $table = 'thong_tin_nguoi_dung';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ngay_sinh' => 'date:Y-m-d',
            'trinh_do_hoc_van' => 'array',
            'suc_khoe_the_chat' => 'array',
            'kha_nang_tai_chinh' => 'array',
            'vi_tri_dia_ly' => 'array',
            'edu_coin' => 'integer',
            'xu_he_thong' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
