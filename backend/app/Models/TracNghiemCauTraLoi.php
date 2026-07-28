<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cau_hoi_id',
    'noi_dung_cau_tra_loi',
    'diem',
])]
class TracNghiemCauTraLoi extends Model
{
    protected $table = 'trac_nghiem_cau_tra_loi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'diem' => 'float',
        ];
    }

    public function cauHoi(): BelongsTo
    {
        return $this->belongsTo(TracNghiemCauHoi::class, 'cau_hoi_id');
    }
}
