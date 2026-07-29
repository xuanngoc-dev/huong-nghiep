<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cau_hoi_id')
                ->nullable()
                ->constrained('trac_nghiem_cau_hoi')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('cau_tra_loi_id')
                ->nullable()
                ->constrained('trac_nghiem_cau_tra_loi')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('nguoi_dung_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('nganh_hoc_id')
                ->nullable()
                ->constrained('danh_muc_nganh_hoc')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('chuyen_nganh_id')
                ->nullable()
                ->constrained('danh_muc_chuyen_nganh')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->decimal('diem_so', 8, 2)->nullable();
            $table->string('ssid', 64);
            $table->timestamps();

            $table->index('ssid');
            $table->index('cau_hoi_id');
            $table->index('cau_tra_loi_id');
            $table->index('nguoi_dung_id');
            $table->index('nganh_hoc_id');
            $table->index('chuyen_nganh_id');
            // Chỉ áp unique khi đã có câu hỏi (bản ghi start có cau_hoi_id = null)
            $table->unique(['ssid', 'cau_hoi_id'], 'trac_nghiem_lich_su_ssid_cau_hoi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_lich_su_tra_loi');
    }
};
