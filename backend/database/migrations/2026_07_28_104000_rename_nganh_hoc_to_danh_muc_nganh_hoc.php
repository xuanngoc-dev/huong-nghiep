<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('nganh_hoc') && ! Schema::hasTable('danh_muc_nganh_hoc')) {
            Schema::rename('nganh_hoc', 'danh_muc_nganh_hoc');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('danh_muc_nganh_hoc') && ! Schema::hasTable('nganh_hoc')) {
            Schema::rename('danh_muc_nganh_hoc', 'nganh_hoc');
        }
    }
};
