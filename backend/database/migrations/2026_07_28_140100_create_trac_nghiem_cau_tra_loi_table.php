<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trac_nghiem_cau_tra_loi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cau_hoi_id')
                ->constrained('trac_nghiem_cau_hoi')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('noi_dung_cau_tra_loi');
            $table->decimal('diem', 8, 2)->default(0);
            $table->timestamps();

            $table->index('cau_hoi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trac_nghiem_cau_tra_loi');
    }
};
