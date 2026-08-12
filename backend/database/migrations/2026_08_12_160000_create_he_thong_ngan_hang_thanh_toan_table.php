<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('he_thong_ngan_hang_thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->string('ten_ngan_hang');
            $table->string('ten_viet_tat', 50)->nullable();
            $table->string('hinh_anh_logo')->nullable();
            $table->string('so_tai_khoan', 50);
            $table->string('chu_tai_khoan');
            $table->string('chi_nhanh')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('trang_thai');
            $table->index('so_tai_khoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('he_thong_ngan_hang_thanh_toan');
    }
};
