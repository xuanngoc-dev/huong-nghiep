<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nganh_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('ten_nganh');
            $table->string('ma_nganh')->unique();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nganh_hoc');
    }
};
