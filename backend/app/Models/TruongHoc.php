<?php

namespace App\Models;

use App\Enums\TrangThaiTruongHoc;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ten_truong',
    'ten_truong_tieng_anh',
    'slug_ten_truong',
    'ma_truong',
    'loai_hinh_truong_id',
    'he_dao_tao_id',
    'tinh_thanh_id',
    'nam_hoc',
    'nam_thanh_lap',
    'so_dien_thoai',
    'hotline',
    'fax',
    'email',
    'website',
    'facebook',
    'youtube',
    'logo',
    'nguoi_dai_dien',
    'ma_so_thue',
    'dia_chi',
    'ghi_chu',
    'mo_ta_thong_tin_tuyen_sinh',
    'thu_tu',
    'trang_thai',
])]
class TruongHoc extends Model
{
    protected $table = 'danh_muc_truong_hoc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiTruongHoc::class,
            'nam_thanh_lap' => 'integer',
            'thu_tu' => 'integer',
        ];
    }

    public function loaiHinhTruong(): BelongsTo
    {
        return $this->belongsTo(LoaiTruong::class, 'loai_hinh_truong_id');
    }

    public function heDaoTao(): BelongsTo
    {
        return $this->belongsTo(HeDaoTao::class, 'he_dao_tao_id');
    }

    public function tinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'tinh_thanh_id');
    }
}
