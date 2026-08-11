<?php

namespace Database\Seeders;

use App\Enums\TrangThaiTonGiao;
use App\Models\TonGiao;
use Illuminate\Database\Seeder;

class TonGiaoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['ma_ton_giao' => '01', 'ten_ton_giao' => 'Phật giáo'],
            ['ma_ton_giao' => '02', 'ten_ton_giao' => 'Công giáo'],
            ['ma_ton_giao' => '03', 'ten_ton_giao' => 'Phật giáo Hòa Hảo'],
            ['ma_ton_giao' => '04', 'ten_ton_giao' => 'Hồi giáo'],
            ['ma_ton_giao' => '05', 'ten_ton_giao' => 'Cao Đài'],
            ['ma_ton_giao' => '06', 'ten_ton_giao' => 'Minh sư đạo'],
            ['ma_ton_giao' => '07', 'ten_ton_giao' => 'Minh Lý đạo'],
            ['ma_ton_giao' => '08', 'ten_ton_giao' => 'Tin Lành'],
            ['ma_ton_giao' => '09', 'ten_ton_giao' => 'Tịnh độ cư sĩ Phật hồi Việt Nam'],
            ['ma_ton_giao' => '10', 'ten_ton_giao' => 'Đạo tứ ấn hiếu nghĩa'],
            ['ma_ton_giao' => '11', 'ten_ton_giao' => 'Bửu sơn Kỳ Hương'],
            ['ma_ton_giao' => '12', 'ten_ton_giao' => "Ba Ha'i"],
            ['ma_ton_giao' => '13', 'ten_ton_giao' => 'Tôn giáo khác'],
            ['ma_ton_giao' => '99', 'ten_ton_giao' => 'Không'],
        ];

        foreach ($items as $item) {
            TonGiao::query()->updateOrCreate(
                ['ma_ton_giao' => $item['ma_ton_giao']],
                [
                    'ten_ton_giao' => $item['ten_ton_giao'],
                    'trang_thai' => TrangThaiTonGiao::DangSuDung,
                ],
            );
        }
    }
}
