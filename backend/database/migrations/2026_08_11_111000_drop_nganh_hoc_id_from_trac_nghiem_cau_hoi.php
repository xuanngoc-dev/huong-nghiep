<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trac_nghiem_cau_hoi')
            && Schema::hasColumn('trac_nghiem_cau_hoi', 'nganh_hoc_id')) {
            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->dropForeign(['nganh_hoc_id']);
                $table->dropColumn('nganh_hoc_id');
            });
        }

        if (Schema::hasTable('trac_nghiem_lich_su_tra_loi')
            && Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'nganh_hoc_id')) {
            DB::statement('ALTER TABLE `trac_nghiem_lich_su_tra_loi` MODIFY `nganh_hoc_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('trac_nghiem_cau_hoi')
            && ! Schema::hasColumn('trac_nghiem_cau_hoi', 'nganh_hoc_id')) {
            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->foreignId('nganh_hoc_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('danh_muc_nganh_hoc')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
                $table->index('nganh_hoc_id');
            });
        }

        if (Schema::hasTable('trac_nghiem_lich_su_tra_loi')
            && Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'nganh_hoc_id')) {
            DB::statement('ALTER TABLE `trac_nghiem_lich_su_tra_loi` MODIFY `nganh_hoc_id` BIGINT UNSIGNED NULL');
        }
    }
};
