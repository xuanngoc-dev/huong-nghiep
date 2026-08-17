<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_lich_su_thanh_toan_edu_coin', function (Blueprint $table) {
            $table->id();
            $table->string('noi_dung', 64);
            $table->unsignedBigInteger('so_tien')->default(0);
            $table->dateTime('thoi_gian');
            $table->text('mo_ta')->nullable();
            $table->timestamps();

            $table->index('noi_dung');
            $table->index('thoi_gian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_lich_su_thanh_toan_edu_coin');
    }
};
