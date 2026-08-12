<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'thong_tin_thanh_toan')) {
            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->json('thong_tin_thanh_toan')->nullable()->after('nhom_nganh');
            });
        }

        if (! Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'chi_tiet_ket_qua')) {
            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->json('chi_tiet_ket_qua')->nullable()->after('thong_tin_thanh_toan');
            });
        }

        if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'nganh_hoc')) {
            DB::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')
                ->orderBy('id')
                ->select(['id', 'nganh_hoc', 'nhom_nganh', 'chi_tiet_ket_qua'])
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        if ($row->chi_tiet_ket_qua !== null) {
                            continue;
                        }

                        $nganhHoc = $this->decodeJsonColumn($row->nganh_hoc);
                        $nhomNganh = $this->decodeJsonColumn($row->nhom_nganh);

                        DB::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')
                            ->where('id', $row->id)
                            ->update([
                                'chi_tiet_ket_qua' => json_encode([
                                    'nganh_hoc' => $nganhHoc ?? [],
                                    'nhom_nganh' => $nhomNganh ?? [],
                                ], JSON_UNESCAPED_UNICODE),
                            ]);
                    }
                });

            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->dropColumn('nganh_hoc');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'nganh_hoc')) {
            Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
                $table->json('nganh_hoc')->nullable()->after('ssid');
            });
        }

        if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'chi_tiet_ket_qua')) {
            DB::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')
                ->orderBy('id')
                ->select(['id', 'chi_tiet_ket_qua'])
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $chiTiet = $this->decodeJsonColumn($row->chi_tiet_ket_qua);
                        $nganhHoc = is_array($chiTiet) ? ($chiTiet['nganh_hoc'] ?? $chiTiet) : [];

                        DB::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh')
                            ->where('id', $row->id)
                            ->update([
                                'nganh_hoc' => json_encode($nganhHoc, JSON_UNESCAPED_UNICODE),
                            ]);
                    }
                });
        }

        Schema::table('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', function (Blueprint $table) {
            if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'thong_tin_thanh_toan')) {
                $table->dropColumn('thong_tin_thanh_toan');
            }

            if (Schema::hasColumn('trac_nghiem_phien_trac_nghiem_da_hoan_thanh', 'chi_tiet_ket_qua')) {
                $table->dropColumn('chi_tiet_ket_qua');
            }
        });
    }

    private function decodeJsonColumn(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
};
