<?php

namespace Database\Seeders;

use App\Enums\TrangThaiDanToc;
use App\Models\DanToc;
use Illuminate\Database\Seeder;

class DanTocSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ten_dan_toc' => 'Ba-na',
                'ma_dan_toc' => '13',
                'ten_goi_khac' => 'BơNâm, Roh, Kon Kđe, Ala Kông, Kpang Kông',
            ],
            [
                'ten_dan_toc' => 'Bố Y',
                'ma_dan_toc' => '49',
                'ten_goi_khac' => 'Chủng chá, Trọng, Gia...',
            ],
            [
                'ten_dan_toc' => 'Brâu',
                'ma_dan_toc' => '52',
                'ten_goi_khac' => 'Brao',
            ],
            [
                'ten_dan_toc' => 'Bru-Vân Kiều',
                'ma_dan_toc' => '23',
                'ten_goi_khac' => 'Bru, Vân Kiều',
            ],
            [
                'ten_dan_toc' => 'Chăm',
                'ma_dan_toc' => '17',
                'ten_goi_khac' => 'Chiêm, Chiêm thành, Chăm pa, Hời....',
            ],
            [
                'ten_dan_toc' => 'Chơ-ro',
                'ma_dan_toc' => '32',
                'ten_goi_khac' => 'Châu Ro, Dơ Ro, Chro, Thượng',
            ],
            [
                'ten_dan_toc' => 'Chu-ru',
                'ma_dan_toc' => '36',
                'ten_goi_khac' => 'Chơ Ru, Kru, Thượng',
            ],
            [
                'ten_dan_toc' => 'Chứt',
                'ma_dan_toc' => '44',
                'ten_goi_khac' => 'Rục, Arem, Sách',
            ],
            [
                'ten_dan_toc' => 'Co',
                'ma_dan_toc' => '30',
                'ten_goi_khac' => 'Cua, Trầu',
            ],
            [
                'ten_dan_toc' => 'Cơ Lao',
                'ma_dan_toc' => '47',
                'ten_goi_khac' => 'Tứ Đư, Ho Ki, Voa Đề',
            ],
            [
                'ten_dan_toc' => 'Cơ-ho',
                'ma_dan_toc' => '16',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Cống',
                'ma_dan_toc' => '48',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Cơ-tu',
                'ma_dan_toc' => '26',
                'ten_goi_khac' => 'Ca Tu, Ka Tu',
            ],
            [
                'ten_dan_toc' => 'Dao',
                'ma_dan_toc' => '09',
                'ten_goi_khac' => 'Mán',
            ],
            [
                'ten_dan_toc' => 'Ê-đê',
                'ma_dan_toc' => '12',
                'ten_goi_khac' => 'Anak ÊĐê, Ra Đê, Ê Đê - Êgar, Đê',
            ],
            [
                'ten_dan_toc' => 'Gia-rai',
                'ma_dan_toc' => '10',
                'ten_goi_khac' => 'Giơ Ray, Chơ Ray',
            ],
            [
                'ten_dan_toc' => 'Giáy',
                'ma_dan_toc' => '25',
                'ten_goi_khac' => 'Nhắng, Giàng',
            ],
            [
                'ten_dan_toc' => 'Gié-Triêng',
                'ma_dan_toc' => '27',
                'ten_goi_khac' => 'Cà Tang, Giang Rẫy',
            ],
            [
                'ten_dan_toc' => 'Hà Nhì',
                'ma_dan_toc' => '35',
                'ten_goi_khac' => 'U Ní, Xá U Ní',
            ],
            [
                'ten_dan_toc' => 'HMông',
                'ma_dan_toc' => '08',
                'ten_goi_khac' => 'Mẹo, Mèo, Miếu Ha, Mán Trắng',
            ],
            [
                'ten_dan_toc' => 'Hoa',
                'ma_dan_toc' => '04',
                'ten_goi_khac' => 'Khách, Hán, Tàu',
            ],
            [
                'ten_dan_toc' => 'Hrê',
                'ma_dan_toc' => '19',
                'ten_goi_khac' => 'Chăm Rê, Chom, Thượng Ba Tơ, Luỹ, Sơn Phòng, Đá Vách, Chăm Quảng Ngãi, Chòm, Rê, Man, Thạch Bích',
            ],
            [
                'ten_dan_toc' => 'Kháng',
                'ma_dan_toc' => '33',
                'ten_goi_khac' => 'Háng, Brển, Xá',
            ],
            [
                'ten_dan_toc' => 'Khơ-me',
                'ma_dan_toc' => '05',
                'ten_goi_khac' => 'Cur, Cul, Cu, Thổ, Việt gốc Miên, Khơ-Me, Krôm',
            ],
            [
                'ten_dan_toc' => 'Khơ-mú',
                'ma_dan_toc' => '29',
                'ten_goi_khac' => 'Xá Cẩu, Khạ Klẩu, Măng Cẩu, Tày Hạy, Mứn Xen, Pu Thểnh, Tểnh',
            ],
            [
                'ten_dan_toc' => 'Kinh',
                'ma_dan_toc' => '01',
                'ten_goi_khac' => 'Kinh',
            ],
            [
                'ten_dan_toc' => 'La Chí',
                'ma_dan_toc' => '38',
                'ten_goi_khac' => 'Cù Tê, La Quả',
            ],
            [
                'ten_dan_toc' => 'La Ha',
                'ma_dan_toc' => '39',
                'ten_goi_khac' => 'Xá Cha, Xá Bung, Xá Khao, Xá Tẩu Nhạ, Xá Poọng, Xá Uống, Bú Hả, Pụa',
            ],
            [
                'ten_dan_toc' => 'La Hủ',
                'ma_dan_toc' => '41',
                'ten_goi_khac' => 'Xá lá vàng, Cò xung, Khù Sung, Kha Quy, Cọ Sọ, Nê Thú',
            ],
            [
                'ten_dan_toc' => 'Lào',
                'ma_dan_toc' => '37',
                'ten_goi_khac' => 'Phu Thay, Phu Lào',
            ],
            [
                'ten_dan_toc' => 'Lô Lô',
                'ma_dan_toc' => '43',
                'ten_goi_khac' => 'Mùn Di, Di, Màn Di, La Ha, Qua La, Ô man, Lu Lộc Màn',
            ],
            [
                'ten_dan_toc' => 'Lự',
                'ma_dan_toc' => '42',
                'ten_goi_khac' => 'Phù Lừ, Nhuồn, Duổn',
            ],
            [
                'ten_dan_toc' => 'Mạ',
                'ma_dan_toc' => '28',
                'ten_goi_khac' => 'Châu Mạ, Chô Mạ, Chê Mạ',
            ],
            [
                'ten_dan_toc' => 'Mảng',
                'ma_dan_toc' => '45',
                'ten_goi_khac' => 'Mảng Ư, Xá Mảng, Niểng O, Xả Bả O',
            ],
            [
                'ten_dan_toc' => 'Mnông',
                'ma_dan_toc' => '20',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Mường',
                'ma_dan_toc' => '06',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Ngái',
                'ma_dan_toc' => '11',
                'ten_goi_khac' => 'Ngái Hắc Cá, Ngái Lầu Mần, Hẹ, Sín, Đàn, Lê, Xuyến',
            ],
            [
                'ten_dan_toc' => 'Người nước ngoài',
                'ma_dan_toc' => '55',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Nùng',
                'ma_dan_toc' => '07',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Ơ Đu',
                'ma_dan_toc' => '53',
                'ten_goi_khac' => 'Tày Hạt',
            ],
            [
                'ten_dan_toc' => 'Pà Thẻn',
                'ma_dan_toc' => '46',
                'ten_goi_khac' => 'Mèo Lai, Mèo Hoa, Mèo Đỏ, Bất Tiên Tộc...',
            ],
            [
                'ten_dan_toc' => 'Phù Lá',
                'ma_dan_toc' => '40',
                'ten_goi_khac' => 'Xá Phô, Cần Thin',
            ],
            [
                'ten_dan_toc' => 'Pu Péo',
                'ma_dan_toc' => '51',
                'ten_goi_khac' => 'La Quả, Penti Lô Lô',
            ],
            [
                'ten_dan_toc' => 'Ra-glai',
                'ma_dan_toc' => '21',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Rơ-măm',
                'ma_dan_toc' => '54',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Sán Chay',
                'ma_dan_toc' => '15',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Sán Dìu',
                'ma_dan_toc' => '18',
                'ten_goi_khac' => 'Trại, Trại Đất, Mần Quần Cộc, Mần Vầy Xé',
            ],
            [
                'ten_dan_toc' => 'Si La',
                'ma_dan_toc' => '50',
                'ten_goi_khac' => 'Kha Pé',
            ],
            [
                'ten_dan_toc' => 'Ta-ôi',
                'ma_dan_toc' => '31',
                'ten_goi_khac' => '',
            ],
            [
                'ten_dan_toc' => 'Tày',
                'ma_dan_toc' => '02',
                'ten_goi_khac' => 'Thổ',
            ],
            [
                'ten_dan_toc' => 'Thái',
                'ma_dan_toc' => '03',
                'ten_goi_khac' => 'Tay Thanh, Man Thanh, Tay Mười, Tay Mường, Hàng Tổng, Tay Do, Thổ',
            ],
            [
                'ten_dan_toc' => 'Thổ',
                'ma_dan_toc' => '24',
                'ten_goi_khac' => 'Người Nhà Làng, Mường, Con Kha, Xá Lá Vàng',
            ],
            [
                'ten_dan_toc' => 'Xinh-mun',
                'ma_dan_toc' => '34',
                'ten_goi_khac' => 'Puộc, Xá, Pnạ',
            ],
            [
                'ten_dan_toc' => 'Xơ-đăng',
                'ma_dan_toc' => '14',
                'ten_goi_khac' => 'Xê Đăng, Kmrâng, Con Lan, Brila',
            ],
            [
                'ten_dan_toc' => 'Xtiêng',
                'ma_dan_toc' => '22',
                'ten_goi_khac' => 'Xa Điêng, Xa Chiêng',
            ],
        ];

        foreach ($items as $item) {
            DanToc::query()->updateOrCreate(
                ['ma_dan_toc' => $item['ma_dan_toc']],
                [
                    'ten_dan_toc' => $item['ten_dan_toc'],
                    'ten_goi_khac' => $item['ten_goi_khac'] !== '' ? $item['ten_goi_khac'] : null,
                    'trang_thai' => TrangThaiDanToc::DangSuDung,
                ],
            );
        }
    }
}
