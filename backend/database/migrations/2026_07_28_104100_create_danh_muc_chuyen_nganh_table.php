<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_chuyen_nganh', function (Blueprint $table) {
            $table->id();
            $table->string('ma_chuyen_nganh')->unique();
            $table->string('ten_chuyen_nganh');
            $table->foreignId('nganh_hoc_id')
                ->constrained('danh_muc_nganh_hoc')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->text('mo_ta')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('nganh_hoc_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_chuyen_nganh');
    }
};
