<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'diem_chuan')) {
                $table->decimal('diem_chuan', 5, 2)->nullable()->after('chi_tieu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            if (Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'diem_chuan')) {
                $table->dropColumn('diem_chuan');
            }
        });
    }
};
