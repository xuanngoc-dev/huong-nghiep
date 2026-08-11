<?php

namespace App\Models;

use App\Enums\TrangThaiNhomNganh;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_nhom_nganh', 'mo_ta', 'trang_thai'])]
class NhomNganh extends Model
{
    protected $table = 'danh_muc_nhom_nganh';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiNhomNganh::class,
        ];
    }
}
