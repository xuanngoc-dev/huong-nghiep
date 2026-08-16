<?php

namespace App\Models;

use App\Enums\KenhThanhToan;
use App\Enums\TrangThaiYeuCauNapEduCoin;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nguoi_nap_id',
    'nguoi_duyet_id',
    'ma_giao_dich',
    'so_luong_edu_coin',
    'so_tien_nap',
    'kenh_thanh_toan',
    'thong_tin_thanh_toan',
    'trang_thai',
    'ghi_chu',
])]
class NapEduCoin extends Model
{
    public const TY_GIA = 1000;

    public const SO_LUONG_MIN = 1;

    public const SO_LUONG_MAX = 10000;

    protected $table = 'he_thong_yeu_cau_nap_edu_coin';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'so_luong_edu_coin' => 'integer',
            'so_tien_nap' => 'integer',
            'kenh_thanh_toan' => KenhThanhToan::class,
            'thong_tin_thanh_toan' => 'array',
            'trang_thai' => TrangThaiYeuCauNapEduCoin::class,
        ];
    }

    public function nguoiNap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_nap_id');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_duyet_id');
    }
}
