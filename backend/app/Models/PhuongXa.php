<?php

namespace App\Models;

use App\Enums\TrangThaiPhuongXa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ten_phuong_xa', 'ma_phuong_xa', 'ma_tinh_thanh', 'trang_thai'])]
class PhuongXa extends Model
{
    protected $table = 'danh_muc_phuong_xa';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiPhuongXa::class,
        ];
    }

    public function tinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'ma_tinh_thanh', 'ma_tinh_thanh');
    }
}
