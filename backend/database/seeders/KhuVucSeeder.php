<?php

namespace Database\Seeders;

use App\Enums\TrangThaiKhuVuc;
use App\Models\KhuVuc;
use Illuminate\Database\Seeder;

class KhuVucSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ma_khu_vuc' => 'KV-BGDLL',
                'ten_khu_vuc' => 'Khu vực biên giới trên đất liền',
                'ghi_chu' => null,
            ],
            [
                'ma_khu_vuc' => 'KV-BGB',
                'ten_khu_vuc' => 'Khu vực biên giới trên biển',
                'ghi_chu' => 'Vùng giáp ranh trên biển, tuyến/không gian biên giới biển theo quản lý',
            ],
            [
                'ma_khu_vuc' => 'KV-HI',
                'ten_khu_vuc' => 'Khu vực hải đảo',
                'ghi_chu' => 'Các đảo, quần đảo, vùng đảo ngoài khơi/ven bờ',
            ],
            [
                'ma_khu_vuc' => 'KV-VSXA-MN',
                'ten_khu_vuc' => 'Vùng sâu vùng xa khu vực miền núi',
                'ghi_chu' => 'Địa hình cao, bị chia cắt, hạ tầng khó tiếp cận',
            ],
            [
                'ma_khu_vuc' => 'KV-VSXA-TL',
                'ten_khu_vuc' => 'Vùng sâu vùng xa khu vực thung lũng/sông suối',
                'ghi_chu' => 'Khó đi lại, phụ thuộc mùa/đường nước',
            ],
            [
                'ma_khu_vuc' => 'KV-DBKK-GT',
                'ten_khu_vuc' => 'Vùng đặc biệt khó khăn do điều kiện giao thông',
                'ghi_chu' => 'Ít đường, xa trung tâm, đi lại khó',
            ],
            [
                'ma_khu_vuc' => 'KV-DCT',
                'ten_khu_vuc' => 'Vùng dân cư thưa',
                'ghi_chu' => 'Mật độ dân thấp, chi phí phục vụ cao, tiếp cận dịch vụ hạn chế',
            ],
            [
                'ma_khu_vuc' => 'KV-DTTS',
                'ten_khu_vuc' => 'Vùng đồng bào dân tộc thiểu số',
                'ghi_chu' => 'Khu vực có đặc thù văn hoá–xã hội và thường trùng địa bàn khó khăn',
            ],
            [
                'ma_khu_vuc' => 'KV-TXTT',
                'ten_khu_vuc' => 'Vùng thường xuyên chịu thiên tai',
                'ghi_chu' => 'Bão lũ, sạt lở, lốc xoáy…; có thể gồm cả miền núi và ven biển/đảo',
            ],
            [
                'ma_khu_vuc' => 'KV-CCDH',
                'ten_khu_vuc' => 'Vùng bị chia cắt mạnh bởi địa hình/địa vật',
                'ghi_chu' => 'Sông lớn, núi cao, vực sâu, khu vực cách trở',
            ],
        ];

        foreach ($items as $item) {
            KhuVuc::query()->updateOrCreate(
                ['ma_khu_vuc' => $item['ma_khu_vuc']],
                [
                    'ten_khu_vuc' => $item['ten_khu_vuc'],
                    'ghi_chu' => $item['ghi_chu'],
                    'trang_thai' => TrangThaiKhuVuc::DangSuDung,
                ],
            );
        }
    }
}
