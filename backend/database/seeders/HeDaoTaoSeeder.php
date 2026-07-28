<?php

namespace Database\Seeders;

use App\Models\HeDaoTao;
use Illuminate\Database\Seeder;

class HeDaoTaoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ma_he_dao_tao' => 'CQ',
                'ten_he_dao_tao' => 'Chính quy',
                'ghi_chu' => 'Đào tạo tập trung toàn thời gian tại cơ sở',
            ],
            [
                'ma_he_dao_tao' => 'LT',
                'ten_he_dao_tao' => 'Liên thông',
                'ghi_chu' => 'Đào tạo liên thông từ trình độ thấp hơn lên cao hơn',
            ],
            [
                'ma_he_dao_tao' => 'VHVL',
                'ten_he_dao_tao' => 'Vừa làm vừa học',
                'ghi_chu' => 'Đào tạo không tập trung, học ngoài giờ hành chính',
            ],
            [
                'ma_he_dao_tao' => 'TX',
                'ten_he_dao_tao' => 'Từ xa',
                'ghi_chu' => 'Đào tạo từ xa qua hình thức trực tuyến/tài liệu',
            ],
            [
                'ma_he_dao_tao' => 'VB2',
                'ten_he_dao_tao' => 'Văn bằng 2',
                'ghi_chu' => 'Đào tạo lấy văn bằng thứ hai cùng trình độ',
            ],
            [
                'ma_he_dao_tao' => 'SDH',
                'ten_he_dao_tao' => 'Sau đại học',
                'ghi_chu' => 'Đào tạo thạc sĩ, tiến sĩ và chuyên khoa',
            ],
            [
                'ma_he_dao_tao' => 'CLC',
                'ten_he_dao_tao' => 'Chất lượng cao',
                'ghi_chu' => 'Chương trình đào tạo chất lượng cao',
            ],
            [
                'ma_he_dao_tao' => 'QT',
                'ten_he_dao_tao' => 'Chương trình quốc tế',
                'ghi_chu' => 'Chương trình đào tạo theo chuẩn/liên kết quốc tế',
            ],
        ];

        foreach ($items as $item) {
            HeDaoTao::query()->updateOrCreate(
                ['ma_he_dao_tao' => $item['ma_he_dao_tao']],
                [
                    'ten_he_dao_tao' => $item['ten_he_dao_tao'],
                    'ghi_chu' => $item['ghi_chu'],
                ],
            );
        }
    }
}
