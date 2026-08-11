<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->string('ho_ten');
            $table->date('ngay_sinh')->nullable();
            $table->string('gioi_tinh', 20)->nullable();
            $table->string('email')->unique();
            $table->string('so_dien_thoai', 30)->nullable();
            $table->string('mat_khau');
            $table->string('dan_toc')->nullable();
            $table->string('ton_giao')->nullable();
            $table->json('trinh_do_hoc_van')->nullable();
            $table->json('suc_khoe_the_chat')->nullable();
            $table->json('kha_nang_tai_chinh')->nullable();
            $table->json('vi_tri_dia_ly')->nullable();
            $table->timestamps();

            $table->index('so_dien_thoai');
            $table->index('gioi_tinh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nguoi_dung');
    }
};
