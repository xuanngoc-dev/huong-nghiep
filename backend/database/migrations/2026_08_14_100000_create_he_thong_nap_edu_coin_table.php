<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('he_thong_nap_edu_coin', function (Blueprint $table) {
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
            $table->unsignedBigInteger('so_luong_edu_coin');
            $table->unsignedBigInteger('so_tien_nap')->default(0);
            $table->string('kenh_thanh_toan', 30);
            $table->json('thong_tin_thanh_toan')->nullable();
            $table->string('trang_thai', 20)->default('cho_duyet');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('kenh_thanh_toan');
            $table->index('trang_thai');
            $table->index(['nguoi_nap_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('he_thong_nap_edu_coin');
    }
};
