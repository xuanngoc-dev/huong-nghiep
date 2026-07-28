<?php

namespace App\Models;

use App\Enums\TrangThaiNganhHoc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_nganh', 'ma_nganh', 'ghi_chu', 'trang_thai'])]
class NganhHoc extends Model
{
    protected $table = 'nganh_hoc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiNganhHoc::class,
        ];
    }
}
