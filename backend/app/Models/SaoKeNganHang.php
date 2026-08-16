<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'gateway',
    'transaction_date',
    'account_number',
    'sub_account',
    'code',
    'content',
    'transfer_type',
    'description',
    'transfer_amount',
    'reference_code',
    'accumulated',
    'item_id',
])]
class SaoKeNganHang extends Model
{
    protected $table = 'he_thong_sao_ke_ngan_hang';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'transfer_amount' => 'integer',
            'accumulated' => 'integer',
            'item_id' => 'integer',
        ];
    }
}
