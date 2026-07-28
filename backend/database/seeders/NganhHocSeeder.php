<?php

namespace Database\Seeders;

use App\Enums\TrangThaiNganhHoc;
use App\Models\NganhHoc;
use Illuminate\Database\Seeder;

class NganhHocSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['ma_nganh' => 'CNTT', 'ten_nganh' => 'Công nghệ thông tin', 'ghi_chu' => 'Ngành trọng điểm về phần mềm và hệ thống'],
            ['ma_nganh' => 'KTPM', 'ten_nganh' => 'Kỹ thuật phần mềm', 'ghi_chu' => 'Tập trung phát triển và bảo trì phần mềm'],
            ['ma_nganh' => 'KHMT', 'ten_nganh' => 'Khoa học máy tính', 'ghi_chu' => 'Nền tảng toán học và thuật toán'],
            ['ma_nganh' => 'ATTT', 'ten_nganh' => 'An toàn thông tin', 'ghi_chu' => 'Bảo mật mạng và dữ liệu'],
            ['ma_nganh' => 'HTTT', 'ten_nganh' => 'Hệ thống thông tin', 'ghi_chu' => 'Phân tích và quản trị hệ thống doanh nghiệp'],
            ['ma_nganh' => 'TTNT', 'ten_nganh' => 'Trí tuệ nhân tạo', 'ghi_chu' => 'Machine learning và deep learning'],
            ['ma_nganh' => 'KHDL', 'ten_nganh' => 'Khoa học dữ liệu', 'ghi_chu' => 'Phân tích dữ liệu lớn và thống kê'],
            ['ma_nganh' => 'MMT', 'ten_nganh' => 'Mạng máy tính và truyền thông', 'ghi_chu' => 'Hạ tầng mạng và truyền thông số'],
            ['ma_nganh' => 'TKDH', 'ten_nganh' => 'Thiết kế đồ họa', 'ghi_chu' => 'Thiết kế hình ảnh, thương hiệu và UI'],
            ['ma_nganh' => 'TKSO', 'ten_nganh' => 'Thiết kế số', 'ghi_chu' => 'Thiết kế sản phẩm số và trải nghiệm'],
            ['ma_nganh' => 'QTKD', 'ten_nganh' => 'Quản trị kinh doanh', 'ghi_chu' => 'Quản lý và vận hành doanh nghiệp'],
            ['ma_nganh' => 'KT', 'ten_nganh' => 'Kế toán', 'ghi_chu' => 'Kế toán tài chính và kiểm toán'],
            ['ma_nganh' => 'TCNH', 'ten_nganh' => 'Tài chính - Ngân hàng', 'ghi_chu' => 'Tài chính doanh nghiệp và ngân hàng'],
            ['ma_nganh' => 'MK', 'ten_nganh' => 'Marketing', 'ghi_chu' => 'Marketing truyền thống và số'],
            ['ma_nganh' => 'TMĐT', 'ten_nganh' => 'Thương mại điện tử', 'ghi_chu' => 'Kinh doanh trên nền tảng số'],
            ['ma_nganh' => 'QTNL', 'ten_nganh' => 'Quản trị nhân lực', 'ghi_chu' => 'Tuyển dụng, đào tạo và phát triển nhân sự'],
            ['ma_nganh' => 'DL', 'ten_nganh' => 'Du lịch', 'ghi_chu' => 'Quản trị và phát triển du lịch'],
            ['ma_nganh' => 'NHKS', 'ten_nganh' => 'Quản trị nhà hàng - khách sạn', 'ghi_chu' => 'Vận hành dịch vụ lưu trú và F&B'],
            ['ma_nganh' => 'NNAnh', 'ten_nganh' => 'Ngôn ngữ Anh', 'ghi_chu' => 'Biên phiên dịch và giảng dạy tiếng Anh'],
            ['ma_nganh' => 'NNNhat', 'ten_nganh' => 'Ngôn ngữ Nhật', 'ghi_chu' => 'Tiếng Nhật thương mại và biên dịch'],
            ['ma_nganh' => 'NNHan', 'ten_nganh' => 'Ngôn ngữ Hàn', 'ghi_chu' => 'Tiếng Hàn thương mại và biên dịch'],
            ['ma_nganh' => 'TT', 'ten_nganh' => 'Truyền thông đa phương tiện', 'ghi_chu' => 'Nội dung số, video và truyền thông'],
            ['ma_nganh' => 'BC', 'ten_nganh' => 'Báo chí', 'ghi_chu' => 'Báo chí và truyền thông đại chúng'],
            ['ma_nganh' => 'XD', 'ten_nganh' => 'Kỹ thuật xây dựng', 'ghi_chu' => 'Thiết kế và thi công công trình'],
            ['ma_nganh' => 'KTOT', 'ten_nganh' => 'Kỹ thuật ô tô', 'ghi_chu' => 'Công nghệ và bảo dưỡng ô tô'],
            ['ma_nganh' => 'DTVT', 'ten_nganh' => 'Điện tử viễn thông', 'ghi_chu' => 'Thiết bị điện tử và hệ thống viễn thông'],
            ['ma_nganh' => 'CK', 'ten_nganh' => 'Công nghệ kỹ thuật cơ khí', 'ghi_chu' => 'Chế tạo máy và cơ khí chính xác'],
            ['ma_nganh' => 'LOG', 'ten_nganh' => 'Logistics và quản lý chuỗi cung ứng', 'ghi_chu' => 'Vận tải, kho bãi và chuỗi cung ứng'],
            ['ma_nganh' => 'LUAT', 'ten_nganh' => 'Luật', 'ghi_chu' => 'Luật dân sự, hình sự và kinh tế'],
            ['ma_nganh' => 'YKH', 'ten_nganh' => 'Y khoa', 'ghi_chu' => 'Đào tạo bác sĩ đa khoa'],
        ];

        $ngungIndexes = [8, 19, 24]; // một số ngành ngừng sử dụng để đa dạng dữ liệu

        foreach ($items as $index => $item) {
            NganhHoc::query()->updateOrCreate(
                ['ma_nganh' => $item['ma_nganh']],
                [
                    'ten_nganh' => $item['ten_nganh'],
                    'ghi_chu' => $item['ghi_chu'],
                    'trang_thai' => in_array($index, $ngungIndexes, true)
                        ? TrangThaiNganhHoc::NgungSuDung
                        : TrangThaiNganhHoc::DangSuDung,
                ],
            );
        }
    }
}
