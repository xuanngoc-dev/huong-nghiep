<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_loai_cau_hoi', function (Blueprint $table) {
            $table->id();
            $table->string('ten_loai_cau_hoi');
            $table->string('ma_loai_cau_hoi', 50)->unique();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_loai_cau_hoi');
    }
};
