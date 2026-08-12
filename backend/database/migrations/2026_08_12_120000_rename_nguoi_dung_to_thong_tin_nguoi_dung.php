<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'so_dien_thoai')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('so_dien_thoai', 30)->nullable()->after('email');
                $table->index('so_dien_thoai');
            });
        }

        if (Schema::hasTable('nguoi_dung') && ! Schema::hasTable('thong_tin_nguoi_dung')) {
            Schema::rename('nguoi_dung', 'thong_tin_nguoi_dung');
        }

        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        if (! Schema::hasColumn('thong_tin_nguoi_dung', 'user_id')) {
            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        $this->backfillUserLinks();

        $duplicates = DB::table('thong_tin_nguoi_dung')
            ->select('user_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('thong_tin_nguoi_dung')
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        DB::table('thong_tin_nguoi_dung')->whereNull('user_id')->delete();

        $this->dropIndexIfExists('thong_tin_nguoi_dung', 'nguoi_dung_email_unique');
        $this->dropIndexIfExists('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_email_unique');
        $this->dropIndexIfExists('thong_tin_nguoi_dung', 'nguoi_dung_so_dien_thoai_index');
        $this->dropIndexIfExists('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_so_dien_thoai_index');

        $dropColumns = array_values(array_filter([
            Schema::hasColumn('thong_tin_nguoi_dung', 'email') ? 'email' : null,
            Schema::hasColumn('thong_tin_nguoi_dung', 'so_dien_thoai') ? 'so_dien_thoai' : null,
            Schema::hasColumn('thong_tin_nguoi_dung', 'mat_khau') ? 'mat_khau' : null,
        ]));

        if ($dropColumns !== []) {
            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }

        DB::statement('ALTER TABLE thong_tin_nguoi_dung MODIFY user_id BIGINT UNSIGNED NOT NULL');

        if (! $this->hasForeignKey('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_user_id_foreign')) {
            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }

        if (! $this->hasIndex('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_user_id_unique')) {
            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        if ($this->hasForeignKey('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_user_id_foreign')) {
            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
        $this->dropIndexIfExists('thong_tin_nguoi_dung', 'thong_tin_nguoi_dung_user_id_unique');

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            if (! Schema::hasColumn('thong_tin_nguoi_dung', 'email')) {
                $table->string('email')->nullable()->after('gioi_tinh');
            }
            if (! Schema::hasColumn('thong_tin_nguoi_dung', 'so_dien_thoai')) {
                $table->string('so_dien_thoai', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('thong_tin_nguoi_dung', 'mat_khau')) {
                $table->string('mat_khau')->nullable()->after('so_dien_thoai');
            }
        });

        if (Schema::hasColumn('thong_tin_nguoi_dung', 'user_id')) {
            $rows = DB::table('thong_tin_nguoi_dung')
                ->join('users', 'users.id', '=', 'thong_tin_nguoi_dung.user_id')
                ->select(
                    'thong_tin_nguoi_dung.id',
                    'users.email',
                    'users.so_dien_thoai',
                    'users.password',
                )
                ->get();

            foreach ($rows as $row) {
                DB::table('thong_tin_nguoi_dung')
                    ->where('id', $row->id)
                    ->update([
                        'email' => $row->email,
                        'so_dien_thoai' => $row->so_dien_thoai,
                        'mat_khau' => $row->password,
                    ]);
            }

            Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            if (Schema::hasColumn('thong_tin_nguoi_dung', 'email')) {
                $table->unique('email');
            }
            if (Schema::hasColumn('thong_tin_nguoi_dung', 'so_dien_thoai')) {
                $table->index('so_dien_thoai');
            }
        });

        if (Schema::hasTable('thong_tin_nguoi_dung') && ! Schema::hasTable('nguoi_dung')) {
            Schema::rename('thong_tin_nguoi_dung', 'nguoi_dung');
        }

        if (Schema::hasColumn('users', 'so_dien_thoai')) {
            $this->dropIndexIfExists('users', 'users_so_dien_thoai_index');
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('so_dien_thoai');
            });
        }
    }

    private function backfillUserLinks(): void
    {
        if (! Schema::hasColumn('thong_tin_nguoi_dung', 'email')) {
            return;
        }

        $rows = DB::table('thong_tin_nguoi_dung')
            ->whereNull('user_id')
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $email = strtolower(trim((string) ($row->email ?? '')));
            if ($email === '') {
                continue;
            }

            $user = DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->first();

            if (! $user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $row->ho_ten ?: $email,
                    'email' => $email,
                    'so_dien_thoai' => $row->so_dien_thoai,
                    'password' => $row->mat_khau ?: bcrypt(Str::random(32)),
                    'role' => 'user',
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            } else {
                $userId = $user->id;
                if (empty($user->so_dien_thoai) && ! empty($row->so_dien_thoai)) {
                    DB::table('users')
                        ->where('id', $userId)
                        ->update(['so_dien_thoai' => $row->so_dien_thoai]);
                }
            }

            DB::table('thong_tin_nguoi_dung')
                ->where('id', $row->id)
                ->update(['user_id' => $userId]);
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if ($this->hasIndex($table, $index)) {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $database = Schema::getConnection()->getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index],
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        $database = Schema::getConnection()->getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.table_constraints
             WHERE table_schema = ?
               AND table_name = ?
               AND constraint_name = ?
               AND constraint_type = ?',
            [$database, $table, $foreignKey, 'FOREIGN KEY'],
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
