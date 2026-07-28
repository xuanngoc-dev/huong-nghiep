<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_truong_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('ten_truong');
            $table->string('ten_truong_tieng_anh')->nullable();
            $table->string('slug_ten_truong')->unique();
            $table->string('ma_truong', 50)->unique();
            $table->foreignId('loai_hinh_truong_id')
                ->nullable()
                ->constrained('danh_muc_loai_truong')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('he_dao_tao_id')
                ->nullable()
                ->constrained('danh_muc_he_dao_tao')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('tinh_thanh_id')
                ->nullable()
                ->constrained('danh_muc_tinh_thanh')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('nam_hoc', 20)->nullable();
            $table->unsignedSmallInteger('nam_thanh_lap')->nullable();
            $table->string('so_dien_thoai', 30)->nullable();
            $table->string('hotline', 30)->nullable();
            $table->string('fax', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('youtube')->nullable();
            $table->string('logo')->nullable();
            $table->string('nguoi_dai_dien')->nullable();
            $table->string('ma_so_thue', 50)->nullable();
            $table->string('dia_chi')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->longText('mo_ta_thong_tin_tuyen_sinh')->nullable();
            $table->unsignedInteger('thu_tu')->default(0);
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('loai_hinh_truong_id');
            $table->index('he_dao_tao_id');
            $table->index('tinh_thanh_id');
            $table->index('trang_thai');
            $table->index('thu_tu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_truong_hoc');
    }
};
