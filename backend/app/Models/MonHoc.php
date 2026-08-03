<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_mon_hoc', 'ma_mon_hoc', 'ghi_chu'])]
class MonHoc extends Model
{
    protected $table = 'danh_muc_mon_hoc';
}
