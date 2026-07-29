<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
            $table->string('ma_loai_cau_hoi', 50)->nullable()->after('ssid');
            $table->index(['ssid', 'ma_loai_cau_hoi'], 'trac_nghiem_lich_su_ssid_ma_loai_index');
        });

        // Backfill từ câu hỏi / loại câu hỏi hiện có
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("
                UPDATE trac_nghiem_lich_su_tra_loi AS ls
                INNER JOIN trac_nghiem_cau_hoi AS ch ON ch.id = ls.cau_hoi_id
                INNER JOIN danh_muc_loai_cau_hoi AS l ON l.id = ch.loai_cau_hoi_id
                SET ls.ma_loai_cau_hoi = LOWER(l.ma_loai_cau_hoi)
                WHERE ls.cau_hoi_id IS NOT NULL
            ");
        } else {
            $rows = DB::table('trac_nghiem_lich_su_tra_loi as ls')
                ->join('trac_nghiem_cau_hoi as ch', 'ch.id', '=', 'ls.cau_hoi_id')
                ->join('danh_muc_loai_cau_hoi as l', 'l.id', '=', 'ch.loai_cau_hoi_id')
                ->select(['ls.id', 'l.ma_loai_cau_hoi'])
                ->get();

            foreach ($rows as $row) {
                DB::table('trac_nghiem_lich_su_tra_loi')
                    ->where('id', $row->id)
                    ->update([
                        'ma_loai_cau_hoi' => strtolower(trim((string) $row->ma_loai_cau_hoi)),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
            $table->dropIndex('trac_nghiem_lich_su_ssid_ma_loai_index');
            $table->dropColumn('ma_loai_cau_hoi');
        });
    }
};
