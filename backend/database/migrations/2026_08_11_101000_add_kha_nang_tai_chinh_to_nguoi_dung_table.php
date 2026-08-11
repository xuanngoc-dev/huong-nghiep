<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (! Schema::hasColumn('nguoi_dung', 'kha_nang_tai_chinh')) {
                $table->json('kha_nang_tai_chinh')
                    ->nullable()
                    ->after('suc_khoe_the_chat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            if (Schema::hasColumn('nguoi_dung', 'kha_nang_tai_chinh')) {
                $table->dropColumn('kha_nang_tai_chinh');
            }
        });
    }
};
