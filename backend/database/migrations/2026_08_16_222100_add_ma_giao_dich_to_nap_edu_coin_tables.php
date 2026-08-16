<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'he_thong_yeu_cau_nap_edu_coin',
        'he_thong_lich_su_nap_edu_coin',
        'trac_nghiem_lich_su_thanh_toan',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'ma_giao_dich')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('ma_giao_dich', 32)->nullable()->after('id');
                $table->unique('ma_giao_dich');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'ma_giao_dich')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['ma_giao_dich']);
                $table->dropColumn('ma_giao_dich');
            });
        }
    }
};
