<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ten_to_hop', 'mon_hoc_ids', 'ghi_chu'])]
class ToHopMonHoc extends Model
{
    protected $table = 'danh_muc_to_hop_mon_hoc';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mon_hoc_ids' => 'array',
        ];
    }
}
