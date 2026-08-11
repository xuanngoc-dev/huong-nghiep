<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_nhom_nganh', function (Blueprint $table) {
            $table->id();
            $table->string('ten_nhom_nganh');
            $table->text('mo_ta')->nullable();
            $table->string('trang_thai', 20)->default('dang_su_dung');
            $table->timestamps();

            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_nhom_nganh');
    }
};
