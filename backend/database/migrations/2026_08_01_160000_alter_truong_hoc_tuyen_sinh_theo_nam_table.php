<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unique cũ có thể đã bị drop ở lần chạy trước (migration fail giữa chừng).
        try {
            Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
                $table->dropUnique('uq_truong_hoc_ts_theo_nam');
            });
        } catch (\Throwable) {
            // ignore
        }

        if (Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'chuyen_nganh_tuyen_sinh_ids')) {
            Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
                $table->dropColumn('chuyen_nganh_tuyen_sinh_ids');
            });
        }

        if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'chuyen_nganh_tuyen_sinh_id')) {
            Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
                $table->unsignedBigInteger('chuyen_nganh_tuyen_sinh_id')
                    ->nullable()
                    ->after('nganh_hoc_tuyen_sinh_id');
            });
        }

        Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'phuong_thuc_xet_tuyen')) {
                $table->string('phuong_thuc_xet_tuyen')->nullable()->after('chuyen_nganh_tuyen_sinh_id');
            }
            if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'to_hop_xet_tuyen')) {
                $table->string('to_hop_xet_tuyen')->nullable()->after('phuong_thuc_xet_tuyen');
            }
            if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'chi_tieu')) {
                $table->unsignedInteger('chi_tieu')->nullable()->after('to_hop_xet_tuyen');
            }
            if (! Schema::hasColumn('truong_hoc_tuyen_sinh_theo_nam', 'ghi_chu')) {
                $table->text('ghi_chu')->nullable()->after('chi_tieu');
            }
        });

        Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            $table->foreign('chuyen_nganh_tuyen_sinh_id', 'th_ts_theo_nam_chuyen_nganh_fk')
                ->references('id')
                ->on('danh_muc_chuyen_nganh')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unique(
                ['ma_truong', 'nam_hoc', 'nganh_hoc_tuyen_sinh_id', 'chuyen_nganh_tuyen_sinh_id'],
                'uq_truong_hoc_ts_theo_nam',
            );
        });
    }

    public function down(): void
    {
        Schema::table('truong_hoc_tuyen_sinh_theo_nam', function (Blueprint $table) {
            $table->dropUnique('uq_truong_hoc_ts_theo_nam');
            $table->dropForeign('th_ts_theo_nam_chuyen_nganh_fk');
            $table->dropColumn([
                'chuyen_nganh_tuyen_sinh_id',
                'phuong_thuc_xet_tuyen',
                'to_hop_xet_tuyen',
                'chi_tieu',
                'ghi_chu',
            ]);
            $table->json('chuyen_nganh_tuyen_sinh_ids')->nullable()->after('nganh_hoc_tuyen_sinh_id');
            $table->unique(
                ['ma_truong', 'nam_hoc', 'nganh_hoc_tuyen_sinh_id'],
                'uq_truong_hoc_ts_theo_nam',
            );
        });
    }
};
