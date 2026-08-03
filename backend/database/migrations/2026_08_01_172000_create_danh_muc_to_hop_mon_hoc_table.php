<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_muc_to_hop_mon_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('ten_to_hop');
            $table->json('mon_hoc_ids')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->index('ten_to_hop');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_muc_to_hop_mon_hoc');
    }
};
