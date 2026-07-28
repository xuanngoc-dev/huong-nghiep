<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc_loai_cau_hoi', function (Blueprint $table) {
            $table->unsignedInteger('thu_tu_uu_tien')
                ->default(1)
                ->after('ghi_chu');

            $table->index('thu_tu_uu_tien');
        });
    }

    public function down(): void
    {
        Schema::table('danh_muc_loai_cau_hoi', function (Blueprint $table) {
            $table->dropIndex(['thu_tu_uu_tien']);
            $table->dropColumn('thu_tu_uu_tien');
        });
    }
};
