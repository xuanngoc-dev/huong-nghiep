<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_cau_hoi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nganh_hoc_id')
                ->constrained('danh_muc_nganh_hoc')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('chuyen_nganh_id')
                ->constrained('danh_muc_chuyen_nganh')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('loai_cau_hoi_id')
                ->constrained('danh_muc_loai_cau_hoi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->text('noi_dung_cau_hoi');
            $table->text('ghi_chu')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('nganh_hoc_id');
            $table->index('chuyen_nganh_id');
            $table->index('loai_cau_hoi_id');
            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_cau_hoi');
    }
};
