<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('he_thong_nap_edu_coin')
            && ! Schema::hasTable('he_thong_yeu_cau_nap_edu_coin')
        ) {
            Schema::rename('he_thong_nap_edu_coin', 'he_thong_yeu_cau_nap_edu_coin');
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('he_thong_yeu_cau_nap_edu_coin')
            && ! Schema::hasTable('he_thong_nap_edu_coin')
        ) {
            Schema::rename('he_thong_yeu_cau_nap_edu_coin', 'he_thong_nap_edu_coin');
        }
    }
};
