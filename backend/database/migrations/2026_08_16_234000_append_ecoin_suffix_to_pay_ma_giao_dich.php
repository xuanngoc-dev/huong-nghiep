<?php

use App\Support\MaGiaoDich;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $codeTables = [
        'trac_nghiem_lich_su_phien',
        'trac_nghiem_lich_su_thanh_toan',
    ];

    public function up(): void
    {
        foreach ($this->codeTables as $table) {
            $this->upgradeMaGiaoDichColumn($table);
            $this->upgradeThongTinThanhToan($table);
        }

        $this->upgradeSaoKeContent();
    }

    public function down(): void
    {
        foreach ($this->codeTables as $table) {
            $this->downgradeMaGiaoDichColumn($table);
            $this->downgradeThongTinThanhToan($table);
        }

        $this->downgradeSaoKeContent();
    }

    private function upgradeMaGiaoDichColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'ma_giao_dich')) {
            return;
        }

        $rows = DB::table($table)
            ->whereNotNull('ma_giao_dich')
            ->where('ma_giao_dich', '!=', '')
            ->get(['id', 'ma_giao_dich']);

        foreach ($rows as $row) {
            $next = MaGiaoDich::canonicalizePay((string) $row->ma_giao_dich);
            if ($next === null || $next === MaGiaoDich::normalize((string) $row->ma_giao_dich)) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update(['ma_giao_dich' => $next]);
        }
    }

    private function downgradeMaGiaoDichColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'ma_giao_dich')) {
            return;
        }

        $rows = DB::table($table)
            ->whereNotNull('ma_giao_dich')
            ->where('ma_giao_dich', '!=', '')
            ->get(['id', 'ma_giao_dich']);

        foreach ($rows as $row) {
            $prev = $this->stripPaySuffix((string) $row->ma_giao_dich);
            if ($prev === null) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update(['ma_giao_dich' => $prev]);
        }
    }

    private function upgradeThongTinThanhToan(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'thong_tin_thanh_toan')) {
            return;
        }

        $rows = DB::table($table)
            ->whereNotNull('thong_tin_thanh_toan')
            ->get(['id', 'thong_tin_thanh_toan']);

        foreach ($rows as $row) {
            $info = $this->decodeJson($row->thong_tin_thanh_toan);
            if (! is_array($info)) {
                continue;
            }

            $changed = false;
            foreach (['ma_giao_dich', 'noi_dung_chuyen_khoan'] as $key) {
                if (! isset($info[$key]) || ! is_string($info[$key])) {
                    continue;
                }
                $next = MaGiaoDich::canonicalizePay($info[$key]);
                if ($next === null || $next === MaGiaoDich::normalize($info[$key])) {
                    continue;
                }
                $info[$key] = $next;
                $changed = true;
            }

            if (! $changed) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update([
                'thong_tin_thanh_toan' => json_encode($info, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function downgradeThongTinThanhToan(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'thong_tin_thanh_toan')) {
            return;
        }

        $rows = DB::table($table)
            ->whereNotNull('thong_tin_thanh_toan')
            ->get(['id', 'thong_tin_thanh_toan']);

        foreach ($rows as $row) {
            $info = $this->decodeJson($row->thong_tin_thanh_toan);
            if (! is_array($info)) {
                continue;
            }

            $changed = false;
            foreach (['ma_giao_dich', 'noi_dung_chuyen_khoan'] as $key) {
                if (! isset($info[$key]) || ! is_string($info[$key])) {
                    continue;
                }
                $prev = $this->stripPaySuffix($info[$key]);
                if ($prev === null) {
                    continue;
                }
                $info[$key] = $prev;
                $changed = true;
            }

            if (! $changed) {
                continue;
            }

            DB::table($table)->where('id', $row->id)->update([
                'thong_tin_thanh_toan' => json_encode($info, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function upgradeSaoKeContent(): void
    {
        if (! Schema::hasTable('he_thong_sao_ke_ngan_hang')
            || ! Schema::hasColumn('he_thong_sao_ke_ngan_hang', 'content')) {
            return;
        }

        $rows = DB::table('he_thong_sao_ke_ngan_hang')
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get(['id', 'content']);

        foreach ($rows as $row) {
            $next = MaGiaoDich::canonicalizePay((string) $row->content);
            if ($next === null || $next === MaGiaoDich::normalize((string) $row->content)) {
                continue;
            }

            DB::table('he_thong_sao_ke_ngan_hang')->where('id', $row->id)->update(['content' => $next]);
        }
    }

    private function downgradeSaoKeContent(): void
    {
        if (! Schema::hasTable('he_thong_sao_ke_ngan_hang')
            || ! Schema::hasColumn('he_thong_sao_ke_ngan_hang', 'content')) {
            return;
        }

        $rows = DB::table('he_thong_sao_ke_ngan_hang')
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->get(['id', 'content']);

        foreach ($rows as $row) {
            $prev = $this->stripPaySuffix((string) $row->content);
            if ($prev === null) {
                continue;
            }

            DB::table('he_thong_sao_ke_ngan_hang')->where('id', $row->id)->update(['content' => $prev]);
        }
    }

    private function stripPaySuffix(string $code): ?string
    {
        $code = MaGiaoDich::normalize($code);
        if (! MaGiaoDich::isValidPay($code)) {
            return null;
        }

        return substr($code, 0, strlen(MaGiaoDich::PREFIX_PAY) + MaGiaoDich::TOKEN_LENGTH);
    }

    private function decodeJson(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }
        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
};
