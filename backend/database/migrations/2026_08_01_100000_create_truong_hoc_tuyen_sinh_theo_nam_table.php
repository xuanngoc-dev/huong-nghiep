<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            $table->id();
            $table->string('ma_truong', 50);
            $table->string('nam_hoc', 20);
            $table->unsignedBigInteger('nganh_hoc_tuyen_sinh_id');
            $table->unsignedBigInteger('chuyen_nganh_tuyen_sinh_id')->nullable();
            $table->string('phuong_thuc_xet_tuyen')->nullable();
            $table->string('to_hop_xet_tuyen')->nullable();
            $table->unsignedInteger('chi_tieu')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->foreign('ma_truong', 'th_ts_theo_nam_ma_truong_fk')
                ->references('ma_truong')
                ->on('danh_muc_truong_hoc')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('nganh_hoc_tuyen_sinh_id', 'th_ts_theo_nam_nganh_fk')
                ->references('id')
                ->on('danh_muc_nganh_hoc')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('chuyen_nganh_tuyen_sinh_id', 'th_ts_theo_nam_chuyen_nganh_fk')
                ->references('id')
                ->on('danh_muc_chuyen_nganh')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(
                ['ma_truong', 'nam_hoc', 'nganh_hoc_tuyen_sinh_id', 'chuyen_nganh_tuyen_sinh_id'],
                'uq_truong_hoc_ts_theo_nam',
            );
            $table->index('ma_truong', 'th_ts_theo_nam_ma_truong_idx');
            $table->index('nam_hoc', 'th_ts_theo_nam_nam_hoc_idx');
            $table->index('nganh_hoc_tuyen_sinh_id', 'th_ts_theo_nam_nganh_idx');
            $table->index('chuyen_nganh_tuyen_sinh_id', 'th_ts_theo_nam_chuyen_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truong_hoc_tuyen_sinh_theo_nam');
    }
};
