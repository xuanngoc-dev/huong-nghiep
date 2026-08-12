<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('he_thong_ngan_hang_thanh_toan', function (Blueprint $table) {
            $table->string('hinh_anh_logo')->nullable()->after('ten_viet_tat');
        });
    }

    public function down(): void
    {
        Schema::table('he_thong_ngan_hang_thanh_toan', function (Blueprint $table) {
            $table->dropColumn('hinh_anh_logo');
        });
    }
};
