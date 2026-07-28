<?php

namespace Database\Seeders;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Models\LoaiCauHoi;
use Illuminate\Database\Seeder;

class LoaiCauHoiSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'ma_loai_cau_hoi' => 'TN',
                'ten_loai_cau_hoi' => 'Trắc nghiệm',
                'ghi_chu' => 'Câu hỏi chọn một hoặc nhiều đáp án',
                'thu_tu_uu_tien' => 1,
            ],
            [
                'ma_loai_cau_hoi' => 'TL',
                'ten_loai_cau_hoi' => 'Tự luận',
                'ghi_chu' => 'Câu hỏi trả lời tự do bằng văn bản',
                'thu_tu_uu_tien' => 2,
            ],
            [
                'ma_loai_cau_hoi' => 'DS',
                'ten_loai_cau_hoi' => 'Đúng / Sai',
                'ghi_chu' => 'Câu hỏi xác nhận đúng hoặc sai',
                'thu_tu_uu_tien' => 3,
            ],
            [
                'ma_loai_cau_hoi' => 'GH',
                'ten_loai_cau_hoi' => 'Ghép đôi',
                'ghi_chu' => 'Câu hỏi nối cặp thông tin tương ứng',
                'thu_tu_uu_tien' => 4,
            ],
            [
                'ma_loai_cau_hoi' => 'SX',
                'ten_loai_cau_hoi' => 'Sắp xếp',
                'ghi_chu' => 'Câu hỏi sắp xếp thứ tự các mục',
                'thu_tu_uu_tien' => 5,
            ],
        ];

        foreach ($items as $item) {
            LoaiCauHoi::query()->updateOrCreate(
                ['ma_loai_cau_hoi' => $item['ma_loai_cau_hoi']],
                [
                    'ten_loai_cau_hoi' => $item['ten_loai_cau_hoi'],
                    'ghi_chu' => $item['ghi_chu'],
                    'thu_tu_uu_tien' => $item['thu_tu_uu_tien'],
                    'trang_thai' => TrangThaiLoaiCauHoi::DangSuDung,
                ],
            );
        }
    }
}
