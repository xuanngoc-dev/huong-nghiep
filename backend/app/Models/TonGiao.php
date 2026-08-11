<?php

namespace App\Models;

use App\Enums\TrangThaiTonGiao;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_ton_giao', 'ma_ton_giao', 'trang_thai'])]
class TonGiao extends Model
{
    protected $table = 'danh_muc_ton_giao';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiTonGiao::class,
        ];
    }
}
