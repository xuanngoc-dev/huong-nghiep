<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_tinh_thanh', function (Blueprint $table) {
            $table->id();
            $table->string('ten_tinh_thanh');
            $table->string('ma_tinh_thanh', 20)->unique();
            $table->foreignId('khu_vuc_id')
                ->nullable()
                ->constrained('danh_muc_khu_vuc')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('khu_vuc_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_tinh_thanh');
    }
};
