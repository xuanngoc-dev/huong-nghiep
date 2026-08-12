<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trac_nghiem_lich_su_tra_loi')
            && ! Schema::hasTable('trac_nghiem_lich_su_tra_loi_chi_tiet')) {
            Schema::rename('trac_nghiem_lich_su_tra_loi', 'trac_nghiem_lich_su_tra_loi_chi_tiet');
        }

        if (Schema::hasTable('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')
            && ! Schema::hasTable('trac_nghiem_lich_su_phien')) {
            Schema::rename('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'trac_nghiem_lich_su_phien');
        }

        if (! Schema::hasTable('trac_nghiem_lich_su_phien')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_lich_su_phien', 'trang_thai')) {
            Schema::table('trac_nghiem_lich_su_phien', function (Blueprint $table) {
                $table->string('trang_thai', 32)
                    ->default('hoan_thanh')
                    ->after('ssid');
                $table->index('trang_thai', 'tn_lich_su_phien_trang_thai_idx');
            });
        }

        // Bản ghi cũ (từ bảng hoàn thành) đều là phiên đã hoàn thành
        DB::table('trac_nghiem_lich_su_phien')
            ->where(function ($query) {
                $query->whereNull('trang_thai')
                    ->orWhere('trang_thai', '');
            })
            ->update(['trang_thai' => 'hoan_thanh']);
    }

    public function down(): void
    {
        if (Schema::hasTable('trac_nghiem_lich_su_phien')
            && Schema::hasColumn('trac_nghiem_lich_su_phien', 'trang_thai')) {
            Schema::table('trac_nghiem_lich_su_phien', function (Blueprint $table) {
                $table->dropIndex('tn_lich_su_phien_trang_thai_idx');
                $table->dropColumn('trang_thai');
            });
        }

        if (Schema::hasTable('trac_nghiem_lich_su_phien')
            && ! Schema::hasTable('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')) {
            Schema::rename('trac_nghiem_lich_su_phien', 'trac_nghiem_phien_trac_nghiem_da_hoan_thanh');
        }

        if (Schema::hasTable('trac_nghiem_lich_su_tra_loi_chi_tiet')
            && ! Schema::hasTable('trac_nghiem_lich_su_tra_loi')) {
            Schema::rename('trac_nghiem_lich_su_tra_loi_chi_tiet', 'trac_nghiem_lich_su_tra_loi');
        }
    }
};
