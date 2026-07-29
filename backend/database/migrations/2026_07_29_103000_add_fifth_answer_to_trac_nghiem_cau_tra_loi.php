<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ANSWER_NOI_DUNG = 'Hoàn toàn phù hợp';

    private const ANSWER_DIEM = 5;

    public function up(): void
    {
        $questionIds = DB::table('trac_nghiem_cau_hoi')
            ->whereNotIn('id', function ($query) {
                $query->select('cau_hoi_id')
                    ->from('trac_nghiem_cau_tra_loi')
                    ->where('diem', self::ANSWER_DIEM);
            })
            ->orderBy('id')
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($questionIds as $cauHoiId) {
            $rows[] = [
                'cau_hoi_id' => $cauHoiId,
                'noi_dung_cau_tra_loi' => self::ANSWER_NOI_DUNG,
                'diem' => self::ANSWER_DIEM,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('trac_nghiem_cau_tra_loi')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('trac_nghiem_cau_tra_loi')
            ->where('noi_dung_cau_tra_loi', self::ANSWER_NOI_DUNG)
            ->where('diem', self::ANSWER_DIEM)
            ->delete();
    }
};
