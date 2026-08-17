<?php

namespace App\Models;

use App\Enums\KenhThanhToan;
use App\Enums\LoaiGiaoDich;
use App\Enums\LoaiKhuyenMai;
use App\Enums\TrangThaiNapEduCoin;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nguoi_nap_id',
    'nguoi_duyet_id',
    'nguoi_tao_id',
    'ma_giao_dich',
    'loai_giao_dich',
    'so_du_truoc_gd',
    'so_du_sau_gd',
    'so_coin_gd',
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
    protected $table = 'he_thong_lich_su_bien_dong_edu_coin';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loai_giao_dich' => LoaiGiaoDich::class,
            'so_du_truoc_gd' => 'integer',
            'so_du_sau_gd' => 'integer',
            'so_coin_gd' => 'integer',
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
