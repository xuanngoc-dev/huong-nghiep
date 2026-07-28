<?php

namespace App\Models;

use App\Enums\TrangThaiTinhThanh;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ten_tinh_thanh', 'ma_tinh_thanh', 'khu_vuc_id', 'trang_thai'])]
class TinhThanh extends Model
{
    protected $table = 'danh_muc_tinh_thanh';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiTinhThanh::class,
        ];
    }

    public function khuVuc(): BelongsTo
    {
        return $this->belongsTo(KhuVuc::class, 'khu_vuc_id');
    }
}
