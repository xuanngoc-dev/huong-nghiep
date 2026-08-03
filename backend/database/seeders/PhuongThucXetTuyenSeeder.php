<?php

namespace Database\Seeders;

use App\Models\PhuongThucXetTuyen;
use Illuminate\Database\Seeder;

class PhuongThucXetTuyenSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ma_phuong_thuc' => 'THPT',
                'ten_phuong_thuc' => 'Xét điểm thi tốt nghiệp THPT',
                'ghi_chu' => 'Xét tuyển dựa trên kết quả kỳ thi tốt nghiệp THPT quốc gia',
            ],
            [
                'ma_phuong_thuc' => 'HOCBA',
                'ten_phuong_thuc' => 'Xét học bạ THPT',
                'ghi_chu' => 'Xét tuyển dựa trên điểm trung bình học bạ lớp 10, 11, 12',
            ],
            [
                'ma_phuong_thuc' => 'DGNL',
                'ten_phuong_thuc' => 'Đánh giá năng lực',
                'ghi_chu' => 'Xét kết quả kỳ thi đánh giá năng lực (VNU, V-SAT...)',
            ],
            [
                'ma_phuong_thuc' => 'DGTD',
                'ten_phuong_thuc' => 'Đánh giá tư duy',
                'ghi_chu' => 'Xét kết quả kỳ thi đánh giá tư duy (HUST...)',
            ],
            [
                'ma_phuong_thuc' => 'XTTHANG',
                'ten_phuong_thuc' => 'Xét tuyển thẳng',
                'ghi_chu' => 'Tuyển thẳng theo quy chế của Bộ GD&ĐT hoặc quy định riêng của trường',
            ],
            [
                'ma_phuong_thuc' => 'KETHOP',
                'ten_phuong_thuc' => 'Xét tuyển kết hợp',
                'ghi_chu' => 'Kết hợp nhiều tiêu chí: điểm thi, học bạ, chứng chỉ ngoại ngữ...',
            ],
            [
                'ma_phuong_thuc' => 'CCNN',
                'ten_phuong_thuc' => 'Xét chứng chỉ ngoại ngữ / quốc tế',
                'ghi_chu' => 'IELTS, TOEFL, SAT, ACT hoặc chứng chỉ quốc tế tương đương',
            ],
            [
                'ma_phuong_thuc' => 'UTT',
                'ten_phuong_thuc' => 'Ưu tiên xét tuyển',
                'ghi_chu' => 'Ưu tiên xét tuyển theo thành tích học tập, giải thưởng học sinh giỏi...',
            ],
        ];

        foreach ($items as $item) {
            PhuongThucXetTuyen::query()->updateOrCreate(
                ['ma_phuong_thuc' => $item['ma_phuong_thuc']],
                [
                    'ten_phuong_thuc' => $item['ten_phuong_thuc'],
                    'ghi_chu' => $item['ghi_chu'],
                ],
            );
        }
    }
}
