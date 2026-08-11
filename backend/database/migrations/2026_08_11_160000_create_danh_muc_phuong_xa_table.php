<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_phuong_xa', function (Blueprint $table) {
            $table->id();
            $table->string('ten_phuong_xa');
            $table->string('ma_phuong_xa', 20)->unique();
            $table->string('ma_tinh_thanh', 20);
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('ma_tinh_thanh');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_phuong_xa');
    }
};
