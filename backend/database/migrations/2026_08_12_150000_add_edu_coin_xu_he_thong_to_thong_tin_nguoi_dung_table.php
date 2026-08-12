<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            if (! Schema::hasColumn('thong_tin_nguoi_dung', 'edu_coin')) {
                $table->unsignedBigInteger('edu_coin')->default(0)->after('vi_tri_dia_ly');
            }
            if (! Schema::hasColumn('thong_tin_nguoi_dung', 'xu_he_thong')) {
                $table->unsignedBigInteger('xu_he_thong')->default(0)->after('edu_coin');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('thong_tin_nguoi_dung')) {
            return;
        }

        Schema::table('thong_tin_nguoi_dung', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('thong_tin_nguoi_dung', 'edu_coin')) {
                $drop[] = 'edu_coin';
            }
            if (Schema::hasColumn('thong_tin_nguoi_dung', 'xu_he_thong')) {
                $drop[] = 'xu_he_thong';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
