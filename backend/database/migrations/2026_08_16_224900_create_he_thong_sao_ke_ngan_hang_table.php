<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('he_thong_sao_ke_ngan_hang', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->dateTime('transaction_date');
            $table->string('account_number', 50);
            $table->string('sub_account', 50)->nullable();
            $table->string('code', 100)->nullable();
            $table->text('content')->nullable();
            $table->string('transfer_type', 10);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('transfer_amount')->default(0);
            $table->string('reference_code', 100)->nullable();
            $table->unsignedBigInteger('accumulated')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            $table->unique('item_id');
            $table->index('gateway');
            $table->index('account_number');
            $table->index('transaction_date');
            $table->index('transfer_type');
            $table->index('reference_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('he_thong_sao_ke_ngan_hang');
    }
};
