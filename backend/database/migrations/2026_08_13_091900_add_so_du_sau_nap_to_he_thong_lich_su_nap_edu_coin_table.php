<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('he_thong_lich_su_nap_edu_coin', 'so_du_sau_nap')) {
            return;
        }

        Schema::table('he_thong_lich_su_nap_edu_coin', function (Blueprint $table) {
            $table->unsignedBigInteger('so_du_sau_nap')->default(0)->after('so_du_truoc_nap');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('he_thong_lich_su_nap_edu_coin', 'so_du_sau_nap')) {
            return;
        }

        Schema::table('he_thong_lich_su_nap_edu_coin', function (Blueprint $table) {
            $table->dropColumn('so_du_sau_nap');
        });
    }
};
