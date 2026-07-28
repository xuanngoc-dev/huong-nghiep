<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_he_dao_tao', function (Blueprint $table) {
            $table->id();
            $table->string('ten_he_dao_tao');
            $table->string('ma_he_dao_tao', 50)->unique();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_he_dao_tao');
    }
};
