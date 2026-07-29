<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('trac_nghiem_cau_tra_loi')
            ->where('noi_dung_cau_tra_loi', 'Hoàn toàn không phù hợp')
            ->where('diem', 1)
            ->update([
                'noi_dung_cau_tra_loi' => 'Không phù hợp',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('trac_nghiem_cau_tra_loi')
            ->where('noi_dung_cau_tra_loi', 'Không phù hợp')
            ->where('diem', 1)
            ->update([
                'noi_dung_cau_tra_loi' => 'Hoàn toàn không phù hợp',
                'updated_at' => now(),
            ]);
    }
};
