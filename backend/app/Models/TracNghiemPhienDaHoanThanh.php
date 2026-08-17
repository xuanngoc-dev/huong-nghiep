<?php

namespace App\Models;

use App\Enums\TrangThaiLichSuPhien;
use App\Support\MaGiaoDich;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'ssid',
    'ma_giao_dich',
    'trang_thai',
    'nhom_nganh',
    'thong_tin_thanh_toan',
    'chi_tiet_ket_qua',
    'nguoi_khao_sat_id',
])]
class TracNghiemPhienDaHoanThanh extends Model
{
    protected $table = 'trac_nghiem_lich_su_phien';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trang_thai' => TrangThaiLichSuPhien::class,
            'nhom_nganh' => 'array',
            'thong_tin_thanh_toan' => 'array',
            'chi_tiet_ket_qua' => 'array',
        ];
    }

    public function nguoiKhaoSat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_khao_sat_id');
    }

    public function thanhToans(): HasMany
    {
        return $this->hasMany(TracNghiemLichSuThanhToan::class, 'lich_su_phien_id');
    }

    /**
     * Dùng mã PAY đã gán cho phiên; nếu chưa có thì nhận candidate hoặc sinh mã mới.
     */
    public function ensureMaGiaoDich(?string $candidate = null): string
    {
        $existing = MaGiaoDich::canonicalizePay((string) $this->ma_giao_dich);
        if ($existing !== null) {
            if ($existing !== MaGiaoDich::normalize((string) $this->ma_giao_dich)) {
                $this->ma_giao_dich = $existing;
                $this->save();
            }

            return $existing;
        }

        $code = MaGiaoDich::resolveMaThanhToan($candidate) ?? MaGiaoDich::taoMaThanhToan();
        $this->ma_giao_dich = $code;
        $this->save();

        return $code;
    }
}
