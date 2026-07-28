<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_khu_vuc', function (Blueprint $table) {
            $table->id();
            $table->string('ten_khu_vuc');
            $table->string('ma_khu_vuc')->unique();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_khu_vuc');
    }
};
