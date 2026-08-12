<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'trang_thai')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('trang_thai', 32)
                    ->default('dang_hoat_dong')
                    ->after('role');
                $table->index('trang_thai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'trang_thai')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['trang_thai']);
                $table->dropColumn('trang_thai');
            });
        }
    }
};
