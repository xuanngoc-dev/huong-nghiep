<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TABLE = 'he_thong_lich_su_nap_edu_coin';

    private const NEW_TABLE = 'he_thong_lich_su_bien_dong_edu_coin';

    /**
     * @var array<string, string>
     */
    private array $columns = [
        'loai_nap_tien' => 'loai_giao_dich',
        'so_du_truoc_nap' => 'so_du_truoc_gd',
        'so_du_sau_nap' => 'so_du_sau_gd',
        'so_coin_nap' => 'so_coin_gd',
    ];

    public function up(): void
    {
        if (
            Schema::hasTable(self::OLD_TABLE)
            && ! Schema::hasTable(self::NEW_TABLE)
        ) {
            Schema::rename(self::OLD_TABLE, self::NEW_TABLE);
        }

        if (! Schema::hasTable(self::NEW_TABLE)) {
            return;
        }

        foreach ($this->columns as $oldName => $newName) {
            if (
                Schema::hasColumn(self::NEW_TABLE, $oldName)
                && ! Schema::hasColumn(self::NEW_TABLE, $newName)
            ) {
                Schema::table(self::NEW_TABLE, function (Blueprint $table) use ($oldName, $newName) {
                    $table->renameColumn($oldName, $newName);
                });
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::NEW_TABLE)) {
            return;
        }

        foreach (array_flip($this->columns) as $newName => $oldName) {
            if (
                Schema::hasColumn(self::NEW_TABLE, $newName)
                && ! Schema::hasColumn(self::NEW_TABLE, $oldName)
            ) {
                Schema::table(self::NEW_TABLE, function (Blueprint $table) use ($newName, $oldName) {
                    $table->renameColumn($newName, $oldName);
                });
            }
        }

        if (! Schema::hasTable(self::OLD_TABLE)) {
            Schema::rename(self::NEW_TABLE, self::OLD_TABLE);
        }
    }
};
