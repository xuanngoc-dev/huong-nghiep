<?php

namespace App\Models;

use App\Enums\TrangThaiDanToc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_dan_toc', 'ma_dan_toc', 'ten_goi_khac', 'trang_thai'])]
class DanToc extends Model
{
    protected $table = 'danh_muc_dan_toc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiDanToc::class,
        ];
    }
}
