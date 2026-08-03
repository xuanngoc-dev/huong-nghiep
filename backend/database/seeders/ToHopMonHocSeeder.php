<?php

namespace Database\Seeders;

use App\Models\MonHoc;
use App\Models\ToHopMonHoc;
use Illuminate\Database\Seeder;

class ToHopMonHocSeeder extends Seeder
{
    public function run(): void
    {
        $monHocIds = MonHoc::query()
            ->pluck('id', 'ma_mon_hoc')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($monHocIds === []) {
            $this->command?->warn('Chưa có dữ liệu môn học. Chạy MonHocSeeder trước.');

            return;
        }

        $items = [
            [
                'ten_to_hop' => 'A00',
                'mon_codes' => ['TOAN', 'LY', 'HOA'],
                'ghi_chu' => 'Toán, Vật lý, Hóa học',
            ],
            [
                'ten_to_hop' => 'A01',
                'mon_codes' => ['TOAN', 'LY', 'ANH'],
                'ghi_chu' => 'Toán, Vật lý, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'B00',
                'mon_codes' => ['TOAN', 'HOA', 'SINH'],
                'ghi_chu' => 'Toán, Hóa học, Sinh học',
            ],
            [
                'ten_to_hop' => 'B08',
                'mon_codes' => ['TOAN', 'SINH', 'ANH'],
                'ghi_chu' => 'Toán, Sinh học, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'C00',
                'mon_codes' => ['VAN', 'SU', 'DIA'],
                'ghi_chu' => 'Ngữ văn, Lịch sử, Địa lý',
            ],
            [
                'ten_to_hop' => 'C01',
                'mon_codes' => ['VAN', 'TOAN', 'LY'],
                'ghi_chu' => 'Ngữ văn, Toán, Vật lý',
            ],
            [
                'ten_to_hop' => 'D01',
                'mon_codes' => ['VAN', 'TOAN', 'ANH'],
                'ghi_chu' => 'Ngữ văn, Toán, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'D07',
                'mon_codes' => ['TOAN', 'HOA', 'ANH'],
                'ghi_chu' => 'Toán, Hóa học, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'D14',
                'mon_codes' => ['VAN', 'SU', 'ANH'],
                'ghi_chu' => 'Ngữ văn, Lịch sử, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'D15',
                'mon_codes' => ['VAN', 'DIA', 'ANH'],
                'ghi_chu' => 'Ngữ văn, Địa lý, Tiếng Anh',
            ],
            [
                'ten_to_hop' => 'A02',
                'mon_codes' => ['TOAN', 'LY', 'SINH'],
                'ghi_chu' => 'Toán, Vật lý, Sinh học',
            ],
            [
                'ten_to_hop' => 'C03',
                'mon_codes' => ['VAN', 'TOAN', 'SU'],
                'ghi_chu' => 'Ngữ văn, Toán, Lịch sử',
            ],
        ];

        foreach ($items as $item) {
            $ids = [];
            foreach ($item['mon_codes'] as $code) {
                if (! isset($monHocIds[$code])) {
                    continue;
                }
                $ids[] = $monHocIds[$code];
            }

            ToHopMonHoc::query()->updateOrCreate(
                ['ten_to_hop' => $item['ten_to_hop']],
                [
                    'mon_hoc_ids' => $ids,
                    'ghi_chu' => $item['ghi_chu'],
                ],
            );
        }
    }
}
