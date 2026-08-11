<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateCauHoi();
        $this->migrateLichSuTraLoi();
        $this->migratePhienDaHoanThanh();
    }

    public function down(): void
    {
        if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'nhom_nganh')
            && ! Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'chuyen_nganh')) {
            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->renameColumn('nhom_nganh', 'chuyen_nganh');
            });
        }

        if (Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'nhom_nganh_id')
            && ! Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'chuyen_nganh_id')) {
            Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
                $table->dropForeign(['nhom_nganh_id']);
                $table->dropColumn('nhom_nganh_id');
            });

            Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
                $table->foreignId('chuyen_nganh_id')
                    ->nullable()
                    ->after('nganh_hoc_id')
                    ->constrained('danh_muc_chuyen_nganh')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
                $table->index('chuyen_nganh_id');
            });
        }

        if (Schema::hasColumn('trac_nghiem_cau_hoi', 'nhom_nganh_id')
            && ! Schema::hasColumn('trac_nghiem_cau_hoi', 'chuyen_nganh_id')) {
            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->dropForeign(['nhom_nganh_id']);
                $table->dropColumn('nhom_nganh_id');
            });

            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->foreignId('chuyen_nganh_id')
                    ->nullable()
                    ->after('nganh_hoc_id')
                    ->constrained('danh_muc_chuyen_nganh')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
                $table->index('chuyen_nganh_id');
            });
        }
    }

    private function migrateCauHoi(): void
    {
        if (! Schema::hasTable('trac_nghiem_cau_hoi')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_cau_hoi', 'nhom_nganh_id')) {
            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->unsignedBigInteger('nhom_nganh_id')->nullable()->after('nganh_hoc_id');
            });
        }

        $this->backfillNhomNganhId('trac_nghiem_cau_hoi');

        if (Schema::hasColumn('trac_nghiem_cau_hoi', 'chuyen_nganh_id')) {
            Schema::table('trac_nghiem_cau_hoi', function (Blueprint $table) {
                $table->dropForeign(['chuyen_nganh_id']);
                $table->dropColumn('chuyen_nganh_id');
            });
        }

        $this->finalizeNhomNganhColumn('trac_nghiem_cau_hoi');
    }

    private function migrateLichSuTraLoi(): void
    {
        if (! Schema::hasTable('trac_nghiem_lich_su_tra_loi')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'nhom_nganh_id')) {
            Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
                $table->unsignedBigInteger('nhom_nganh_id')->nullable()->after('nganh_hoc_id');
            });
        }

        if (Schema::hasColumn('trac_nghiem_cau_hoi', 'nhom_nganh_id')) {
            DB::statement('
                UPDATE trac_nghiem_lich_su_tra_loi AS ls
                INNER JOIN trac_nghiem_cau_hoi AS ch ON ch.id = ls.cau_hoi_id
                SET ls.nhom_nganh_id = ch.nhom_nganh_id
                WHERE ch.nhom_nganh_id IS NOT NULL
            ');
        }

        $this->backfillNhomNganhId('trac_nghiem_lich_su_tra_loi');

        if (Schema::hasColumn('trac_nghiem_lich_su_tra_loi', 'chuyen_nganh_id')) {
            Schema::table('trac_nghiem_lich_su_tra_loi', function (Blueprint $table) {
                $table->dropForeign(['chuyen_nganh_id']);
                $table->dropColumn('chuyen_nganh_id');
            });
        }

        $this->finalizeNhomNganhColumn('trac_nghiem_lich_su_tra_loi');
    }

    private function migratePhienDaHoanThanh(): void
    {
        if (! Schema::hasTable('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')) {
            return;
        }

        if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'chuyen_nganh')
            && ! Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'nhom_nganh')) {
            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->renameColumn('chuyen_nganh', 'nhom_nganh');
            });
        }
    }

    private function backfillNhomNganhId(string $table): void
    {
        $nganhNhomMap = $this->buildNganhToNhomMap();
        $fallbackNhomId = $this->fallbackNhomNganhId();

        DB::table($table)
            ->whereNull('nhom_nganh_id')
            ->orderBy('id')
            ->select(['id', 'nganh_hoc_id'])
            ->chunkById(200, function ($rows) use ($table, $nganhNhomMap, $fallbackNhomId) {
                foreach ($rows as $row) {
                    $nhomId = $nganhNhomMap[(int) $row->nganh_hoc_id] ?? $fallbackNhomId;
                    if (! $nhomId) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['nhom_nganh_id' => $nhomId]);
                }
            });

        if ($fallbackNhomId) {
            DB::table($table)
                ->whereNull('nhom_nganh_id')
                ->update(['nhom_nganh_id' => $fallbackNhomId]);
        }
    }

    private function finalizeNhomNganhColumn(string $table): void
    {
        $fallbackNhomId = $this->fallbackNhomNganhId();
        if ($fallbackNhomId) {
            DB::table($table)
                ->whereNull('nhom_nganh_id')
                ->update(['nhom_nganh_id' => $fallbackNhomId]);
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `nhom_nganh_id` BIGINT UNSIGNED NOT NULL");

        $fkName = "{$table}_nhom_nganh_id_foreign";
        if (! $this->hasForeignKey($table, $fkName)) {
            Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
                $blueprint->foreign('nhom_nganh_id', $fkName)
                    ->references('id')
                    ->on('danh_muc_nhom_nganh')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }

        $indexName = "{$table}_nhom_nganh_id_index";
        if (! $this->hasIndex($table, $indexName)) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->index('nhom_nganh_id');
            });
        }
    }

    /**
     * @return array<int, int>
     */
    private function buildNganhToNhomMap(): array
    {
        $map = [];

        if (! Schema::hasColumn('danh_muc_nganh_hoc', 'nhom_nganh_ids')) {
            return $map;
        }

        $rows = DB::table('danh_muc_nganh_hoc')->get(['id', 'nhom_nganh_ids']);
        foreach ($rows as $row) {
            $ids = $row->nhom_nganh_ids;
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            if (! is_array($ids) || $ids === []) {
                continue;
            }

            $first = (int) ($ids[0] ?? 0);
            if ($first > 0) {
                $map[(int) $row->id] = $first;
            }
        }

        return $map;
    }

    private function fallbackNhomNganhId(): ?int
    {
        if (! Schema::hasTable('danh_muc_nhom_nganh')) {
            return null;
        }

        $id = DB::table('danh_muc_nhom_nganh')->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        return collect(DB::select(
            'SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = ?',
            [$table, $constraintName, 'FOREIGN KEY'],
        ))->isNotEmpty();
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select(
            'SELECT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?',
            [$table, $indexName],
        ))->isNotEmpty();
    }
};
