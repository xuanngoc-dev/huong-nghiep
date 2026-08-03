<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_phuong_thuc_xet_tuyen', function (Blueprint $table) {
            $table->id();
            $table->string('ma_phuong_thuc', 50)->unique();
            $table->string('ten_phuong_thuc');
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_phuong_thuc_xet_tuyen');
    }
};
