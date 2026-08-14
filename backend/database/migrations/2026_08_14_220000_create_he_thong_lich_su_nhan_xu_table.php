<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('he_thong_lich_su_nhan_xu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_dung_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('hinh_thuc_nhan_xu', 30)->default('diem_danh');
            $table->date('ngay_nhan');
            $table->unsignedBigInteger('so_du_truoc_khi_nhan')->default(0);
            $table->unsignedBigInteger('so_xu_nhan_duoc');
            $table->unsignedBigInteger('so_du_sau_khi_nhan')->default(0);
            $table->string('trang_thai', 20)->default('thanh_cong');
            $table->timestamps();

            $table->index('hinh_thuc_nhan_xu');
            $table->index('trang_thai');
            $table->unique(
                ['nguoi_dung_id', 'hinh_thuc_nhan_xu', 'ngay_nhan'],
                'he_thong_lich_su_nhan_xu_unique_ngay',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('he_thong_lich_su_nhan_xu');
    }
};
