<?php

namespace Database\Seeders;

use App\Enums\TrangThaiTruongHoc;
use App\Models\HeDaoTao;
use App\Models\LoaiTruong;
use App\Models\TinhThanh;
use App\Models\TruongHoc;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TruongHocSeeder extends Seeder
{
    public function run(): void
    {
        $loaiByMa = LoaiTruong::query()->pluck('id', 'ma_loai_truong');
        $heByMa = HeDaoTao::query()->pluck('id', 'ma_he_dao_tao');
        $tinhByMa = TinhThanh::query()->pluck('id', 'ma_tinh_thanh');

        $items = [
            [
                'ma_truong' => 'BKAHN',
                'ten_truong' => 'Đại học Bách khoa Hà Nội',
                'ten_truong_tieng_anh' => 'Hanoi University of Science and Technology',
                'loai_hinh' => 'DH',
                'he_dao_tao' => 'CQ',
                'tinh' => '1',
                'nam_thanh_lap' => 1956,
                'nam_hoc' => '2025-2026',
                'so_dien_thoai' => '02438693333',
                'email' => 'contact@hust.edu.vn',
                'website' => 'https://www.hust.edu.vn',
                'dia_chi' => 'Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội',
                'nguoi_dai_dien' => 'Hiệu trưởng',
                'mo_ta_thong_tin_tuyen_sinh' => 'Tuyển sinh các ngành kỹ thuật, công nghệ và kinh tế kỹ thuật.',
                'thu_tu' => 1,
            ],
            [
                'ma_truong' => 'VNUHN',
                'ten_truong' => 'Đại học Quốc gia Hà Nội',
                'ten_truong_tieng_anh' => 'Vietnam National University, Hanoi',
                'loai_hinh' => 'DH',
                'he_dao_tao' => 'CQ',
                'tinh' => '1',
                'nam_thanh_lap' => 1906,
                'nam_hoc' => '2025-2026',
                'so_dien_thoai' => '02437547607',
                'email' => 'contact@vnu.edu.vn',
                'website' => 'https://www.vnu.edu.vn',
                'dia_chi' => '144 Xuân Thủy, Cầu Giấy, Hà Nội',
                'mo_ta_thong_tin_tuyen_sinh' => 'Hệ thống đại học đa ngành, đa lĩnh vực hàng đầu.',
                'thu_tu' => 2,
            ],
            [
                'ma_truong' => 'UIT',
                'ten_truong' => 'Trường Đại học Công nghệ Thông tin - ĐHQG TP.HCM',
                'ten_truong_tieng_anh' => 'University of Information Technology',
                'loai_hinh' => 'DH',
                'he_dao_tao' => 'CQ',
                'tinh' => '79',
                'nam_thanh_lap' => 2006,
                'nam_hoc' => '2025-2026',
                'so_dien_thoai' => '02837251993',
                'email' => 'info@uit.edu.vn',
                'website' => 'https://www.uit.edu.vn',
                'facebook' => 'https://www.facebook.com/UIT.Fanpage',
                'dia_chi' => 'Khu phố 6, P. Linh Trung, TP. Thủ Đức, TP.HCM',
                'mo_ta_thong_tin_tuyen_sinh' => 'Chuyên đào tạo CNTT, khoa học máy tính và truyền thông.',
                'thu_tu' => 3,
            ],
            [
                'ma_truong' => 'FPTU',
                'ten_truong' => 'Đại học FPT',
                'ten_truong_tieng_anh' => 'FPT University',
                'loai_hinh' => 'TT',
                'he_dao_tao' => 'CQ',
                'tinh' => '1',
                'nam_thanh_lap' => 2006,
                'nam_hoc' => '2025-2026',
                'hotline' => '02473001866',
                'email' => 'tuyensinh@fpt.edu.vn',
                'website' => 'https://daihoc.fpt.edu.vn',
                'facebook' => 'https://www.facebook.com/FPTU.HN',
                'youtube' => 'https://www.youtube.com/@FPTUniversity',
                'dia_chi' => 'Khu Công nghệ cao Hòa Lạc, Thạch Thất, Hà Nội',
                'mo_ta_thong_tin_tuyen_sinh' => 'Tuyển sinh ngành CNTT, kinh tế số, ngôn ngữ theo mô hình thực tiễn.',
                'thu_tu' => 4,
            ],
            [
                'ma_truong' => 'DUT',
                'ten_truong' => 'Đại học Bách khoa - Đại học Đà Nẵng',
                'ten_truong_tieng_anh' => 'The University of Danang - University of Science and Technology',
                'loai_hinh' => 'DH',
                'he_dao_tao' => 'CQ',
                'tinh' => '48',
                'nam_thanh_lap' => 1975,
                'nam_hoc' => '2025-2026',
                'so_dien_thoai' => '02363842567',
                'email' => 'dhbk@dut.udn.vn',
                'website' => 'https://dut.udn.vn',
                'dia_chi' => '54 Nguyễn Lương Bằng, Liên Chiểu, Đà Nẵng',
                'mo_ta_thong_tin_tuyen_sinh' => 'Đào tạo kỹ thuật, công nghệ tại khu vực miền Trung.',
                'thu_tu' => 5,
            ],
        ];

        foreach ($items as $item) {
            $ten = $item['ten_truong'];
            $slug = Str::slug($ten) ?: 'truong-'.Str::lower($item['ma_truong']);

            TruongHoc::query()->updateOrCreate(
                ['ma_truong' => $item['ma_truong']],
                [
                    'ten_truong' => $ten,
                    'ten_truong_tieng_anh' => $item['ten_truong_tieng_anh'],
                    'slug_ten_truong' => $slug,
                    'loai_hinh_truong_id' => $loaiByMa[$item['loai_hinh']] ?? null,
                    'he_dao_tao_id' => $heByMa[$item['he_dao_tao']] ?? null,
                    'tinh_thanh_id' => $tinhByMa[$item['tinh']] ?? null,
                    'nam_hoc' => $item['nam_hoc'] ?? null,
                    'nam_thanh_lap' => $item['nam_thanh_lap'] ?? null,
                    'so_dien_thoai' => $item['so_dien_thoai'] ?? null,
                    'hotline' => $item['hotline'] ?? null,
                    'email' => $item['email'] ?? null,
                    'website' => $item['website'] ?? null,
                    'facebook' => $item['facebook'] ?? null,
                    'youtube' => $item['youtube'] ?? null,
                    'dia_chi' => $item['dia_chi'] ?? null,
                    'nguoi_dai_dien' => $item['nguoi_dai_dien'] ?? null,
                    'mo_ta_thong_tin_tuyen_sinh' => $item['mo_ta_thong_tin_tuyen_sinh'] ?? null,
                    'thu_tu' => $item['thu_tu'] ?? 0,
                    'trang_thai' => TrangThaiTruongHoc::DangSuDung,
                ],
            );
        }
    }
}
