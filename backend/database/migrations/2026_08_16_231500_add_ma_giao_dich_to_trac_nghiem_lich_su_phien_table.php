<?php

use App\Support\MaGiaoDich;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('trac_nghiem_lich_su_phien')) {
            return;
        }

        if (! Schema::hasColumn('trac_nghiem_lich_su_phien', 'ma_giao_dich')) {
            Schema::table('trac_nghiem_lich_su_phien', function (Blueprint $table) {
                $table->string('ma_giao_dich', 32)->nullable()->after('ssid');
                $table->unique('ma_giao_dich');
            });
        }

        $this->backfillFromThanhToan();
        $this->backfillMissingCodes();
    }

    public function down(): void
    {
        if (! Schema::hasTable('trac_nghiem_lich_su_phien')
            || ! Schema::hasColumn('trac_nghiem_lich_su_phien', 'ma_giao_dich')) {
            return;
        }

        Schema::table('trac_nghiem_lich_su_phien', function (Blueprint $table) {
            $table->dropUnique(['ma_giao_dich']);
            $table->dropColumn('ma_giao_dich');
        });
    }

    private function backfillFromThanhToan(): void
    {
        if (! Schema::hasTable('trac_nghiem_lich_su_thanh_toan')) {
            return;
        }

        $rows = DB::table('trac_nghiem_lich_su_thanh_toan')
            ->whereNotNull('ma_giao_dich')
            ->where('ma_giao_dich', '!=', '')
            ->orderBy('id')
            ->get(['lich_su_phien_id', 'ma_giao_dich']);

        $usedCodes = [];
        $usedPhien = [];

        foreach ($rows as $row) {
            $code = strtoupper(trim((string) $row->ma_giao_dich));
            $phienId = (int) $row->lich_su_phien_id;
            if ($code === '' || isset($usedCodes[$code]) || isset($usedPhien[$phienId])) {
                continue;
            }

            $usedCodes[$code] = true;
            $usedPhien[$phienId] = true;

            DB::table('trac_nghiem_lich_su_phien')
                ->where('id', $phienId)
                ->whereNull('ma_giao_dich')
                ->update(['ma_giao_dich' => $code]);
        }
    }

    private function backfillMissingCodes(): void
    {
        $ids = DB::table('trac_nghiem_lich_su_phien')
            ->where(function ($query) {
                $query->whereNull('ma_giao_dich')
                    ->orWhere('ma_giao_dich', '');
            })
            ->pluck('id');

        foreach ($ids as $id) {
            DB::table('trac_nghiem_lich_su_phien')
                ->where('id', $id)
                ->update(['ma_giao_dich' => MaGiaoDich::taoMaThanhToan()]);
        }
    }
};
