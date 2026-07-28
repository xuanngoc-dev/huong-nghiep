<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_loai_truong', function (Blueprint $table) {
            $table->id();
            $table->string('ten_loai_truong');
            $table->string('ma_loai_truong', 50)->unique();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_loai_truong');
    }
};
