<?php

namespace Database\Seeders;

use App\Models\MonHoc;
use Illuminate\Database\Seeder;

class MonHocSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['ma_mon_hoc' => 'TOAN', 'ten_mon_hoc' => 'Toán', 'ghi_chu' => 'Môn Toán học'],
            ['ma_mon_hoc' => 'LY', 'ten_mon_hoc' => 'Vật lý', 'ghi_chu' => 'Môn Vật lý'],
            ['ma_mon_hoc' => 'HOA', 'ten_mon_hoc' => 'Hóa học', 'ghi_chu' => 'Môn Hóa học'],
            ['ma_mon_hoc' => 'SINH', 'ten_mon_hoc' => 'Sinh học', 'ghi_chu' => 'Môn Sinh học'],
            ['ma_mon_hoc' => 'VAN', 'ten_mon_hoc' => 'Ngữ văn', 'ghi_chu' => 'Môn Ngữ văn'],
            ['ma_mon_hoc' => 'SU', 'ten_mon_hoc' => 'Lịch sử', 'ghi_chu' => 'Môn Lịch sử'],
            ['ma_mon_hoc' => 'DIA', 'ten_mon_hoc' => 'Địa lý', 'ghi_chu' => 'Môn Địa lý'],
            ['ma_mon_hoc' => 'ANH', 'ten_mon_hoc' => 'Tiếng Anh', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Anh)'],
            ['ma_mon_hoc' => 'GDCD', 'ten_mon_hoc' => 'Giáo dục công dân', 'ghi_chu' => 'Môn Giáo dục công dân'],
            ['ma_mon_hoc' => 'TIN', 'ten_mon_hoc' => 'Tin học', 'ghi_chu' => 'Môn Tin học'],
            ['ma_mon_hoc' => 'NGA', 'ten_mon_hoc' => 'Tiếng Nga', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Nga)'],
            ['ma_mon_hoc' => 'PHAP', 'ten_mon_hoc' => 'Tiếng Pháp', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Pháp)'],
            ['ma_mon_hoc' => 'TRUNG', 'ten_mon_hoc' => 'Tiếng Trung', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Trung)'],
            ['ma_mon_hoc' => 'NHAT', 'ten_mon_hoc' => 'Tiếng Nhật', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Nhật)'],
            ['ma_mon_hoc' => 'HAN', 'ten_mon_hoc' => 'Tiếng Hàn', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Hàn)'],
            ['ma_mon_hoc' => 'DUC', 'ten_mon_hoc' => 'Tiếng Đức', 'ghi_chu' => 'Môn Ngoại ngữ (Tiếng Đức)'],
        ];

        foreach ($items as $item) {
            MonHoc::query()->updateOrCreate(
                ['ma_mon_hoc' => $item['ma_mon_hoc']],
                [
                    'ten_mon_hoc' => $item['ten_mon_hoc'],
                    'ghi_chu' => $item['ghi_chu'],
                ],
            );
        }
    }
}
