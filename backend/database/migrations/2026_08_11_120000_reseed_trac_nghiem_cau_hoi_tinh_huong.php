<?php

use App\Enums\TrangThaiTracNghiemCauHoi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SEED_MARKER = 'migration:reseed_trac_nghiem_tinh_huong_20260811';

    private const QUESTIONS_PER_PAIR = 10;

    public function up(): void
    {
        if (! Schema::hasTable('trac_nghiem_cau_hoi')
            || ! Schema::hasTable('trac_nghiem_cau_tra_loi')
            || ! Schema::hasColumn('trac_nghiem_cau_hoi', 'nhom_nganh_id')) {
            return;
        }

        $bank = require database_path('data/trac_nghiem_cau_hoi_tinh_huong.php');
        if (! is_array($bank) || $bank === []) {
            return;
        }

        $loaiList = DB::table('danh_muc_loai_cau_hoi')
            ->orderBy('thu_tu_uu_tien')
            ->orderBy('id')
            ->get(['id', 'ma_loai_cau_hoi']);

        $nhomList = DB::table('danh_muc_nhom_nganh')
            ->orderBy('id')
            ->get(['id', 'ten_nhom_nganh']);

        if ($loaiList->isEmpty() || $nhomList->isEmpty()) {
            return;
        }

        $this->wipeQuizData();

        $now = now();
        $questionRows = [];
        $answerPlan = []; // index => list of answers

        foreach ($nhomList as $nhom) {
            $nhomKey = $this->resolveNhomKey((string) $nhom->ten_nhom_nganh, $bank);
            if ($nhomKey === null) {
                continue;
            }

            foreach ($loaiList as $loai) {
                $loaiMa = strtoupper(trim((string) $loai->ma_loai_cau_hoi));
                $items = $bank[$nhomKey]['items'][$loaiMa] ?? [];
                if (! is_array($items) || $items === []) {
                    continue;
                }

                $items = array_values($items);
                $count = min(self::QUESTIONS_PER_PAIR, count($items));

                for ($i = 0; $i < $count; $i++) {
                    $item = $items[$i];
                    $cauHoi = trim((string) ($item['cau_hoi'] ?? ''));
                    $dapAn = $item['dap_an'] ?? [];
                    if ($cauHoi === '' || ! is_array($dapAn) || count($dapAn) < 5) {
                        continue;
                    }

                    $questionRows[] = [
                        'nhom_nganh_id' => (int) $nhom->id,
                        'loai_cau_hoi_id' => (int) $loai->id,
                        'noi_dung_cau_hoi' => $cauHoi,
                        'ghi_chu' => self::SEED_MARKER,
                        'trang_thai' => TrangThaiTracNghiemCauHoi::DangSuDung->value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $answerPlan[] = array_values($dapAn);
                }
            }
        }

        if ($questionRows === []) {
            return;
        }

        foreach (array_chunk($questionRows, 200) as $chunk) {
            DB::table('trac_nghiem_cau_hoi')->insert($chunk);
        }

        $insertedIds = DB::table('trac_nghiem_cau_hoi')
            ->where('ghi_chu', self::SEED_MARKER)
            ->orderBy('id')
            ->pluck('id')
            ->values();

        if ($insertedIds->count() !== count($answerPlan)) {
            throw new RuntimeException(
                'Seed trắc nghiệm tình huống lệch số câu hỏi/đáp án: '
                .$insertedIds->count().' vs '.count($answerPlan),
            );
        }

        $answerRows = [];
        foreach ($insertedIds as $index => $cauHoiId) {
            foreach ($answerPlan[$index] as $answer) {
                $noiDung = trim((string) ($answer['noi_dung'] ?? ''));
                $diem = (int) ($answer['diem'] ?? 0);
                if ($noiDung === '' || $diem < 1 || $diem > 5) {
                    continue;
                }

                $answerRows[] = [
                    'cau_hoi_id' => (int) $cauHoiId,
                    'noi_dung_cau_tra_loi' => $noiDung,
                    'diem' => $diem,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($answerRows, 500) as $chunk) {
            DB::table('trac_nghiem_cau_tra_loi')->insert($chunk);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('trac_nghiem_cau_hoi')) {
            return;
        }

        $questionIds = DB::table('trac_nghiem_cau_hoi')
            ->where('ghi_chu', self::SEED_MARKER)
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('trac_nghiem_cau_tra_loi')) {
            DB::table('trac_nghiem_cau_tra_loi')
                ->whereIn('cau_hoi_id', $questionIds)
                ->delete();
        }

        DB::table('trac_nghiem_cau_hoi')
            ->whereIn('id', $questionIds)
            ->delete();
    }

    private function wipeQuizData(): void
    {
        if (Schema::hasTable('trac_nghiem_lich_su_tra_loi')) {
            // Câu hỏi/đáp án cũ không còn hợp lệ sau khi seed lại.
            DB::table('trac_nghiem_lich_su_tra_loi')->delete();
        }

        if (Schema::hasTable('trac_nghiem_cau_tra_loi')) {
            DB::table('trac_nghiem_cau_tra_loi')->delete();
        }

        DB::table('trac_nghiem_cau_hoi')->delete();
    }

    /**
     * @param  array<string, mixed>  $bank
     */
    private function resolveNhomKey(string $tenNhom, array $bank): ?string
    {
        $haystack = mb_strtolower($tenNhom);

        foreach ($bank as $key => $meta) {
            $matchers = $meta['matchers'] ?? [];
            if (! is_array($matchers)) {
                continue;
            }

            foreach ($matchers as $needle) {
                $needle = mb_strtolower(trim((string) $needle));
                if ($needle !== '' && str_contains($haystack, $needle)) {
                    return (string) $key;
                }
            }
        }

        return null;
    }
};
