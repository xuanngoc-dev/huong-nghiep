<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_loai_truong', 'ma_loai_truong', 'ghi_chu'])]
class LoaiTruong extends Model
{
    protected $table = 'danh_muc_loai_truong';
}
