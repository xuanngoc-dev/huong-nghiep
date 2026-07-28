<?php

namespace Database\Seeders;

use App\Enums\TrangThaiTinhThanh;
use App\Models\KhuVuc;
use App\Models\TinhThanh;
use Illuminate\Database\Seeder;

class TinhThanhSeeder extends Seeder
{
    public function run(): void
    {
        $khuVucIds = KhuVuc::query()->pluck('id', 'ma_khu_vuc');

        // Gán khu vực theo đặc thù nổi bật của từng tỉnh (có thể chỉnh lại trên UI).
        $khuVucByMaTinh = [
            '1' => null, // Hà Nội
            '4' => 'KV-BGDLL', // Cao Bằng — biên giới đất liền
            '8' => 'KV-VSXA-MN', // Tuyên Quang — miền núi
            '11' => 'KV-BGDLL', // Điện Biên
            '12' => 'KV-BGDLL', // Lai Châu
            '14' => 'KV-BGDLL', // Sơn La
            '15' => 'KV-BGDLL', // Lào Cai
            '19' => 'KV-VSXA-MN', // Thái Nguyên
            '20' => 'KV-BGDLL', // Lạng Sơn
            '22' => 'KV-HI', // Quảng Ninh — hải đảo
            '24' => null, // Bắc Ninh
            '25' => 'KV-VSXA-MN', // Phú Thọ
            '31' => 'KV-BGB', // Hải Phòng — biên giới biển
            '33' => null, // Hưng Yên
            '37' => null, // Ninh Bình
            '38' => 'KV-VSXA-MN', // Thanh Hoá
            '40' => 'KV-BGDLL', // Nghệ An — biên giới Lào
            '42' => 'KV-BGDLL', // Hà Tĩnh
            '44' => 'KV-BGDLL', // Quảng Trị
            '46' => 'KV-TXTT', // Huế — thiên tai
            '48' => 'KV-BGB', // Đà Nẵng
            '51' => 'KV-TXTT', // Quảng Ngãi
            '52' => 'KV-DTTS', // Gia Lai — DTTS
            '56' => 'KV-HI', // Khánh Hoà — hải đảo
            '66' => 'KV-VSXA-MN', // Đắk Lắk
            '68' => 'KV-VSXA-MN', // Lâm Đồng
            '75' => null, // Đồng Nai
            '79' => null, // TP.HCM
            '80' => 'KV-BGDLL', // Tây Ninh
            '82' => 'KV-VSXA-TL', // Đồng Tháp — sông nước
            '92' => null, // Cần Thơ
            '96' => 'KV-BGB', // Cà Mau — biên giới biển
            '86' => null, // Vĩnh Long
            '91' => 'KV-BGDLL', // An Giang
            '1000' => null, // Nước ngoài
        ];

        $items = [
            ['ten_tinh_thanh' => 'Thành phố Hà Nội', 'ma_tinh_thanh' => '1'],
            ['ten_tinh_thanh' => 'Tỉnh Cao Bằng', 'ma_tinh_thanh' => '4'],
            ['ten_tinh_thanh' => 'Tỉnh Tuyên Quang', 'ma_tinh_thanh' => '8'],
            ['ten_tinh_thanh' => 'Tỉnh Điện Biên', 'ma_tinh_thanh' => '11'],
            ['ten_tinh_thanh' => 'Tỉnh Lai Châu', 'ma_tinh_thanh' => '12'],
            ['ten_tinh_thanh' => 'Tỉnh Sơn La', 'ma_tinh_thanh' => '14'],
            ['ten_tinh_thanh' => 'Tỉnh Lào Cai', 'ma_tinh_thanh' => '15'],
            ['ten_tinh_thanh' => 'Tỉnh Thái Nguyên', 'ma_tinh_thanh' => '19'],
            ['ten_tinh_thanh' => 'Tỉnh Lạng Sơn', 'ma_tinh_thanh' => '20'],
            ['ten_tinh_thanh' => 'Tỉnh Quảng Ninh', 'ma_tinh_thanh' => '22'],
            ['ten_tinh_thanh' => 'Tỉnh Bắc Ninh', 'ma_tinh_thanh' => '24'],
            ['ten_tinh_thanh' => 'Tỉnh Phú Thọ', 'ma_tinh_thanh' => '25'],
            ['ten_tinh_thanh' => 'Tp Hải Phòng', 'ma_tinh_thanh' => '31'],
            ['ten_tinh_thanh' => 'Tỉnh Hưng Yên', 'ma_tinh_thanh' => '33'],
            ['ten_tinh_thanh' => 'Tỉnh Ninh Bình', 'ma_tinh_thanh' => '37'],
            ['ten_tinh_thanh' => 'Tỉnh Thanh Hoá', 'ma_tinh_thanh' => '38'],
            ['ten_tinh_thanh' => 'Tỉnh Nghệ An', 'ma_tinh_thanh' => '40'],
            ['ten_tinh_thanh' => 'Tỉnh Hà Tĩnh', 'ma_tinh_thanh' => '42'],
            ['ten_tinh_thanh' => 'Tỉnh Quảng Trị', 'ma_tinh_thanh' => '44'],
            ['ten_tinh_thanh' => 'Thành phố Huế', 'ma_tinh_thanh' => '46'],
            ['ten_tinh_thanh' => 'Tp Đà Nẵng', 'ma_tinh_thanh' => '48'],
            ['ten_tinh_thanh' => 'Tỉnh Quảng Ngãi', 'ma_tinh_thanh' => '51'],
            ['ten_tinh_thanh' => 'Tỉnh Gia Lai', 'ma_tinh_thanh' => '52'],
            ['ten_tinh_thanh' => 'Tỉnh Khánh Hoà', 'ma_tinh_thanh' => '56'],
            ['ten_tinh_thanh' => 'Tỉnh Đắk Lắk', 'ma_tinh_thanh' => '66'],
            ['ten_tinh_thanh' => 'Tỉnh Lâm Đồng', 'ma_tinh_thanh' => '68'],
            ['ten_tinh_thanh' => 'Thành phố Đồng Nai', 'ma_tinh_thanh' => '75'],
            ['ten_tinh_thanh' => 'Tp Hồ Chí Minh', 'ma_tinh_thanh' => '79'],
            ['ten_tinh_thanh' => 'Tỉnh Tây Ninh', 'ma_tinh_thanh' => '80'],
            ['ten_tinh_thanh' => 'Tỉnh Đồng Tháp', 'ma_tinh_thanh' => '82'],
            ['ten_tinh_thanh' => 'Tp Cần Thơ', 'ma_tinh_thanh' => '92'],
            ['ten_tinh_thanh' => 'Tỉnh Cà Mau', 'ma_tinh_thanh' => '96'],
            ['ten_tinh_thanh' => 'Tỉnh Vĩnh Long', 'ma_tinh_thanh' => '86'],
            ['ten_tinh_thanh' => 'Tỉnh An Giang', 'ma_tinh_thanh' => '91'],
            ['ten_tinh_thanh' => 'Nước ngoài', 'ma_tinh_thanh' => '1000'],
        ];

        foreach ($items as $item) {
            $maKhuVuc = $khuVucByMaTinh[$item['ma_tinh_thanh']] ?? null;
            $khuVucId = $maKhuVuc ? ($khuVucIds[$maKhuVuc] ?? null) : null;

            TinhThanh::query()->updateOrCreate(
                ['ma_tinh_thanh' => $item['ma_tinh_thanh']],
                [
                    'ten_tinh_thanh' => $item['ten_tinh_thanh'],
                    'khu_vuc_id' => $khuVucId,
                    'trang_thai' => TrangThaiTinhThanh::DangSuDung,
                ],
            );
        }
    }
}
