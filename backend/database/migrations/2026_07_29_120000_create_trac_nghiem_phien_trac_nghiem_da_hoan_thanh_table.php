<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
            $table->id();
            $table->string('ssid', 64);
            $table->json('nganh_hoc')->nullable();
            $table->json('chuyen_nganh')->nullable();
            $table->unsignedBigInteger('nguoi_khao_sat_id')->nullable();
            $table->timestamps();

            $table->unique('ssid', 'tn_phien_hoan_thanh_ssid_unique');
            $table->index('nguoi_khao_sat_id', 'tn_phien_hoan_thanh_nguoi_idx');
            $table->foreign('nguoi_khao_sat_id', 'tn_phien_hoan_thanh_nguoi_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_phien_trac_nghiem_da_hoan_thanh');
    }
};
