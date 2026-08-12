<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung') || ! Schema::hasColumn('thong_tin_nguoi_dung', 'ho_ten')) {
            return;
        }

        // Đồng bộ họ tên sang users.name trước khi bỏ cột.
        $rows = DB::table('thong_tin_nguoi_dung')
            ->whereNotNull('user_id')
            ->whereNotNull('ho_ten')
            ->where('ho_ten', '!=', '')
            ->select('user_id', 'ho_ten')
            ->get();

        foreach ($rows as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->update(['name' => $row->ho_ten]);
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->dropColumn('ho_ten');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung') || Schema::hasColumn('thong_tin_nguoi_dung', 'ho_ten')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $table->string('ho_ten')->nullable()->after('user_id');
        });

        $rows = DB::table('thong_tin_nguoi_dung')
            ->join('users', 'users.id', '=', 'thong_tin_nguoi_dung.user_id')
            ->select('thong_tin_nguoi_dung.id', 'users.name')
            ->get();

        foreach ($rows as $row) {
            DB::table('thong_tin_nguoi_dung')
                ->where('id', $row->id)
                ->update(['ho_ten' => $row->name]);
        }
    }
};
