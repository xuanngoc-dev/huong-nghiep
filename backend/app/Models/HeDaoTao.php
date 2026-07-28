<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_he_dao_tao', 'ma_he_dao_tao', 'ghi_chu'])]
class HeDaoTao extends Model
{
    protected $table = 'danh_muc_he_dao_tao';
}
