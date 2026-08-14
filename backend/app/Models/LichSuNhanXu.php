<?php

namespace App\Models;

use App\Enums\HinhThucNhanXu;
use App\Enums\TrangThaiNhanXu;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nguoi_dung_id',
    'hinh_thuc_nhan_xu',
    'ngay_nhan',
    'so_du_truoc_khi_nhan',
    'so_xu_nhan_duoc',
    'so_du_sau_khi_nhan',
    'trang_thai',
])]
class LichSuNhanXu extends Model
{
    public const SO_XU_DIEM_DANH = 5000;

    public const TIMEZONE = 'Asia/Ho_Chi_Minh';

    protected $table = 'he_thong_lich_su_nhan_xu';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hinh_thuc_nhan_xu' => HinhThucNhanXu::class,
            'ngay_nhan' => 'date:Y-m-d',
            'so_du_truoc_khi_nhan' => 'integer',
            'so_xu_nhan_duoc' => 'integer',
            'so_du_sau_khi_nhan' => 'integer',
            'trang_thai' => TrangThaiNhanXu::class,
        ];
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nguoi_dung_id');
    }
}
