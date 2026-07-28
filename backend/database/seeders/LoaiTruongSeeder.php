<?php

namespace Database\Seeders;

use App\Models\LoaiTruong;
use Illuminate\Database\Seeder;

class LoaiTruongSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ma_loai_truong' => 'CL',
                'ten_loai_truong' => 'Công lập',
                'ghi_chu' => 'Trường do Nhà nước thành lập và quản lý',
            ],
            [
                'ma_loai_truong' => 'TT',
                'ten_loai_truong' => 'Tư thục',
                'ghi_chu' => 'Trường do tổ chức/cá nhân ngoài công lập thành lập',
            ],
            [
                'ma_loai_truong' => 'DL',
                'ten_loai_truong' => 'Dân lập',
                'ghi_chu' => 'Trường do cộng đồng dân cư đóng góp thành lập',
            ],
            [
                'ma_loai_truong' => 'QT',
                'ten_loai_truong' => 'Quốc tế',
                'ghi_chu' => 'Trường áp dụng chương trình/giáo dục quốc tế',
            ],
            [
                'ma_loai_truong' => 'LK',
                'ten_loai_truong' => 'Liên kết',
                'ghi_chu' => 'Trường/chương trình liên kết trong nước hoặc nước ngoài',
            ],
            [
                'ma_loai_truong' => 'CD',
                'ten_loai_truong' => 'Cao đẳng',
                'ghi_chu' => 'Cơ sở đào tạo trình độ cao đẳng',
            ],
            [
                'ma_loai_truong' => 'DH',
                'ten_loai_truong' => 'Đại học',
                'ghi_chu' => 'Cơ sở đào tạo trình độ đại học trở lên',
            ],
            [
                'ma_loai_truong' => 'TC',
                'ten_loai_truong' => 'Trung cấp',
                'ghi_chu' => 'Cơ sở đào tạo trình độ trung cấp nghề/chuyên nghiệp',
            ],
        ];

        foreach ($items as $item) {
            LoaiTruong::query()->updateOrCreate(
                ['ma_loai_truong' => $item['ma_loai_truong']],
                [
                    'ten_loai_truong' => $item['ten_loai_truong'],
                    'ghi_chu' => $item['ghi_chu'],
                ],
            );
        }
    }
}
