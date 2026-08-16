<?php

namespace App\Models;

use App\Enums\HinhThucThanhToanTracNghiem;
use App\Enums\TrangThaiThanhToanTracNghiem;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lich_su_phien_id',
    'nguoi_dung_id',
    'ma_giao_dich',
    'hinh_thuc_thanh_toan',
    'so_tien_thanh_toan',
    'trang_thai',
    'thong_tin_thanh_toan',
])]
class TracNghiemLichSuThanhToan extends Model
{
    public const SO_EDU_COIN = 15;

    public const SO_TIEN_CHUYEN_KHOAN = 15000;

    protected $table = 'trac_nghiem_lich_su_thanh_toan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hinh_thuc_thanh_toan' => HinhThucThanhToanTracNghiem::class,
            'so_tien_thanh_toan' => 'integer',
            'trang_thai' => TrangThaiThanhToanTracNghiem::class,
            'thong_tin_thanh_toan' => 'array',
        ];
    }

    public function lichSuPhien(): BelongsTo
    {
        return $this->belongsTo(TracNghiemPhienDaHoanThanh::class, 'lich_su_phien_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }
}
