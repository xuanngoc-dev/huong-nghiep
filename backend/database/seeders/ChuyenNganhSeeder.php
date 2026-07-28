<?php

namespace Database\Seeders;

use App\Enums\TrangThaiNganhHoc;
use App\Models\ChuyenNganh;
use App\Models\NganhHoc;
use Illuminate\Database\Seeder;

class ChuyenNganhSeeder extends Seeder
{
    public function run(): void
    {
        $nganhByMa = NganhHoc::query()->pluck('id', 'ma_nganh');

        $items = [
            ['ma' => 'CNTT', 'ma_cn' => 'CNTT-PM', 'ten' => 'Công nghệ phần mềm', 'mo_ta' => 'Phát triển phần mềm ứng dụng'],
            ['ma' => 'CNTT', 'ma_cn' => 'CNTT-HT', 'ten' => 'Hệ thống thông tin quản lý', 'mo_ta' => 'Xây dựng hệ thống thông tin doanh nghiệp'],
            ['ma' => 'KTPM', 'ma_cn' => 'KTPM-WEB', 'ten' => 'Phát triển Web', 'mo_ta' => 'Lập trình web frontend và backend'],
            ['ma' => 'KTPM', 'ma_cn' => 'KTPM-MOB', 'ten' => 'Phát triển ứng dụng di động', 'mo_ta' => 'Lập trình ứng dụng iOS/Android'],
            ['ma' => 'KHMT', 'ma_cn' => 'KHMT-AI', 'ten' => 'Trí tuệ nhân tạo ứng dụng', 'mo_ta' => 'Ứng dụng AI trong thực tế'],
            ['ma' => 'ATTT', 'ma_cn' => 'ATTT-BM', 'ten' => 'Bảo mật mạng', 'mo_ta' => 'An ninh mạng và phòng chống tấn'],
            ['ma' => 'TTNT', 'ma_cn' => 'TTNT-ML', 'ten' => 'Học máy', 'mo_ta' => 'Machine learning và mô hình dự đoán'],
            ['ma' => 'KHDL', 'ma_cn' => 'KHDL-BA', 'ten' => 'Phân tích dữ liệu kinh doanh', 'mo_ta' => 'Business intelligence và data analytics'],
            ['ma' => 'QTKD', 'ma_cn' => 'QTKD-DN', 'ten' => 'Quản trị doanh nghiệp', 'mo_ta' => 'Quản lý và vận hành doanh nghiệp'],
            ['ma' => 'QTKD', 'ma_cn' => 'QTKD-QT', 'ten' => 'Quản trị chiến lược', 'mo_ta' => 'Lập và thực thi chiến lược kinh doanh'],
            ['ma' => 'MK', 'ma_cn' => 'MK-SO', 'ten' => 'Marketing số', 'mo_ta' => 'Digital marketing và truyền thông số'],
            ['ma' => 'MK', 'ma_cn' => 'MK-TH', 'ten' => 'Marketing thương hiệu', 'mo_ta' => 'Xây dựng và quản trị thương hiệu'],
            ['ma' => 'TCNH', 'ma_cn' => 'TCNH-DN', 'ten' => 'Tài chính doanh nghiệp', 'mo_ta' => 'Quản trị tài chính công ty'],
            ['ma' => 'TCNH', 'ma_cn' => 'TCNH-NH', 'ten' => 'Ngân hàng thương mại', 'mo_ta' => 'Nghiệp vụ ngân hàng'],
            ['ma' => 'KT', 'ma_cn' => 'KT-KT', 'ten' => 'Kế toán kiểm toán', 'mo_ta' => 'Kế toán và kiểm toán doanh nghiệp'],
            ['ma' => 'DL', 'ma_cn' => 'DL-LH', 'ten' => 'Điều hành tour', 'mo_ta' => 'Tổ chức và điều hành chương trình du lịch'],
            ['ma' => 'NHKS', 'ma_cn' => 'NHKS-KS', 'ten' => 'Quản trị khách sạn', 'mo_ta' => 'Vận hành dịch vụ lưu trú'],
            ['ma' => 'NNAnh', 'ma_cn' => 'NNANH-BP', 'ten' => 'Biên phiên dịch tiếng Anh', 'mo_ta' => 'Biên dịch và phiên dịch chuyên nghiệp'],
            ['ma' => 'XD', 'ma_cn' => 'XD-CT', 'ten' => 'Xây dựng công trình dân dụng', 'mo_ta' => 'Thiết kế và thi công công trình'],
            ['ma' => 'LOG', 'ma_cn' => 'LOG-VT', 'ten' => 'Logistics vận tải', 'mo_ta' => 'Quản lý vận tải và chuỗi cung ứng'],
        ];

        $ngungIndexes = [4, 11, 16];

        foreach ($items as $index => $item) {
            $nganhId = $nganhByMa[$item['ma']] ?? null;
            if (! $nganhId) {
                continue;
            }

            ChuyenNganh::query()->updateOrCreate(
                ['ma_chuyen_nganh' => $item['ma_cn']],
                [
                    'ten_chuyen_nganh' => $item['ten'],
                    'nganh_hoc_id' => $nganhId,
                    'mo_ta' => $item['mo_ta'],
                    'ghi_chu' => null,
                    'trang_thai' => in_array($index, $ngungIndexes, true)
                        ? TrangThaiNganhHoc::NgungSuDung
                        : TrangThaiNganhHoc::DangSuDung,
                ],
            );
        }
    }
}
