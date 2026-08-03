<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ma_phuong_thuc', 'ten_phuong_thuc', 'ghi_chu'])]
class PhuongThucXetTuyen extends Model
{
    protected $table = 'danh_muc_phuong_thuc_xet_tuyen';
}
