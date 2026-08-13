<?php

namespace App\Models;

use App\Enums\KenhThanhToan;
use App\Enums\LoaiKhuyenMai;
use App\Enums\LoaiNapTien;
use App\Enums\TrangThaiNapEduCoin;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nguoi_nap_id',
    'nguoi_duyet_id',
    'nguoi_tao_id',
    'loai_nap_tien',
    'so_du_truoc_nap',
    'so_du_sau_nap',
    'so_coin_nap',
    'so_tien_thanh_toan',
    'loai_khuyen_mai',
    'coin_khuyen_mai',
    'tong_coin_nhan',
    'kenh_thanh_toan',
    'thong_tin_thanh_toan',
    'ghi_chu',
    'trang_thai',
])]
class LichSuNapEduCoin extends Model
{
    protected $table = 'he_thong_lich_su_nap_edu_coin';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loai_nap_tien' => LoaiNapTien::class,
            'so_du_truoc_nap' => 'integer',
            'so_du_sau_nap' => 'integer',
            'so_coin_nap' => 'integer',
            'so_tien_thanh_toan' => 'integer',
            'loai_khuyen_mai' => LoaiKhuyenMai::class,
            'coin_khuyen_mai' => 'integer',
            'tong_coin_nhan' => 'integer',
            'kenh_thanh_toan' => KenhThanhToan::class,
            'thong_tin_thanh_toan' => 'array',
            'trang_thai' => TrangThaiNapEduCoin::class,
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

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_tao_id');
    }
}
