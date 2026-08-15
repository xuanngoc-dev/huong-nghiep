<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_lich_su_thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lich_su_phien_id')
                ->constrained('trac_nghiem_lich_su_phien')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('nguoi_dung_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('hinh_thuc_thanh_toan', 30);
            $table->unsignedBigInteger('so_tien_thanh_toan');
            $table->string('trang_thai', 20)->default('dang_xu_ly');
            $table->json('thong_tin_thanh_toan')->nullable();
            $table->timestamps();

            $table->index('hinh_thuc_thanh_toan');
            $table->index('trang_thai');
            $table->index(['lich_su_phien_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_lich_su_thanh_toan');
    }
};
