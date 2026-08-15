<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung') || ! Schema::hasColumn('thong_tin_nguoi_dung', 'ngay_sinh')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->string('ngay_sinh', 10)->nullable()->change();
        });

        $rows = DB::table('thong_tin_nguoi_dung')
            ->whereNotNull('ngay_sinh')
            ->where('ngay_sinh', '!=', '')
            ->get(['id', 'ngay_sinh']);

        foreach ($rows as $row) {
            $raw = substr(trim((string) $row->ngay_sinh), 0, 10);
            $iso = \DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
            if (! $iso || $iso->format('Y-m-d') !== $raw) {
                continue;
            }

            DB::table('thong_tin_nguoi_dung')
                ->where('id', $row->id)
                ->update(['ngay_sinh' => $iso->format('d/m/Y')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung') || ! Schema::hasColumn('thong_tin_nguoi_dung', 'ngay_sinh')) {
            return;
        }

        $rows = DB::table('thong_tin_nguoi_dung')
            ->whereNotNull('ngay_sinh')
            ->where('ngay_sinh', '!=', '')
            ->get(['id', 'ngay_sinh']);

        foreach ($rows as $row) {
            $raw = trim((string) $row->ngay_sinh);
            $vn = \DateTimeImmutable::createFromFormat('!d/m/Y', $raw);
            if (! $vn || $vn->format('d/m/Y') !== $raw) {
                continue;
            }

            DB::table('thong_tin_nguoi_dung')
                ->where('id', $row->id)
                ->update(['ngay_sinh' => $vn->format('Y-m-d')]);
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->date('ngay_sinh')->nullable()->change();
        });
    }
};
