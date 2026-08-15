<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        if (Schema::hasColumn('thong_tin_nguoi_dung', 'mat_khau_thanh_toan')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->string('mat_khau_thanh_toan')->nullable()->after('xu_he_thong');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        if (! Schema::hasColumn('thong_tin_nguoi_dung', 'mat_khau_thanh_toan')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('mat_khau_thanh_toan');
        });
    }
};
