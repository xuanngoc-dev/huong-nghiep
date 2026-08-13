<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('he_thong_lich_su_nap_edu_coin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_nap_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('nguoi_duyet_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('nguoi_tao_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('loai_nap_tien', 30);
            $table->unsignedBigInteger('so_du_truoc_nap')->default(0);
            $table->unsignedBigInteger('so_du_sau_nap')->default(0);
            $table->unsignedBigInteger('so_coin_nap');
            $table->unsignedBigInteger('so_tien_thanh_toan')->default(0);
            $table->string('loai_khuyen_mai', 20)->nullable();
            $table->unsignedBigInteger('coin_khuyen_mai')->default(0);
            $table->unsignedBigInteger('tong_coin_nhan');
            $table->string('kenh_thanh_toan', 30);
            $table->json('thong_tin_thanh_toan')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_xu_ly');
            $table->timestamps();

            $table->index('loai_nap_tien');
            $table->index('kenh_thanh_toan');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('he_thong_lich_su_nap_edu_coin');
    }
};
