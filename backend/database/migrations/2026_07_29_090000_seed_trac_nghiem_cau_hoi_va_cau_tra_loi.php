<?php

use App\Enums\TrangThaiTracNghiemCauHoi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const QUESTIONS_PER_LOAI = 100;

    private const SEED_MARKER = 'migration:seed_trac_nghiem_cau_hoi_20260729';

    private const ANSWERS = [
        ['noi_dung' => 'Không phù hợp', 'diem' => 1],
        ['noi_dung' => 'Ít phù hợp', 'diem' => 2],
        ['noi_dung' => 'Khá phù hợp', 'diem' => 3],
        ['noi_dung' => 'Rất phù hợp', 'diem' => 4],
        ['noi_dung' => 'Hoàn toàn phù hợp', 'diem' => 5],
    ];

    /**
     * Mẫu câu hỏi theo mã loại — placeholder: {nganh}, {chuyen_nganh}, {stt}
     *
     * @var array<string, list<string>>
     */
    private const QUESTION_TEMPLATES = [
        'STDM' => [
            'Bạn có hứng thú tìm hiểu sâu về lĩnh vực {chuyen_nganh} thuộc ngành {nganh} không?',
            'Bạn cảm thấy đam mê khi được làm việc liên quan đến {chuyen_nganh}?',
            'Bạn thường dành thời gian tự học hoặc theo dõi tin tức về {nganh} — {chuyen_nganh}?',
            'Bạn thích các hoạt động, dự án hoặc trải nghiệm gắn với {chuyen_nganh}?',
            'Bạn hình dung mình gắn bó lâu dài với nghề nghiệp trong lĩnh vực {chuyen_nganh}?',
            'Bạn cảm thấy vui và có động lực khi bàn luận về chủ đề {nganh}?',
        ],
        'KNKN' => [
            'Bạn tự đánh giá mình có năng lực nền tảng phù hợp với {chuyen_nganh}?',
            'Bạn dễ nắm bắt kiến thức và kỹ năng cần thiết của ngành {nganh}?',
            'Bạn tự tin hoàn thành các nhiệm vụ điển hình trong lĩnh vực {chuyen_nganh}?',
            'Bạn có thể áp dụng kỹ năng hiện có vào công việc thuộc {chuyen_nganh}?',
            'Bạn học nhanh các công cụ, quy trình liên quan đến {nganh} — {chuyen_nganh}?',
            'Bạn cảm thấy thế mạnh cá nhân khớp với yêu cầu của {chuyen_nganh}?',
        ],
        'MTLV' => [
            'Bạn phù hợp với môi trường làm việc đặc trưng của lĩnh vực {chuyen_nganh}?',
            'Bạn thích không gian và nhịp độ công việc thường thấy ở ngành {nganh}?',
            'Bạn cảm thấy thoải mái khi làm việc trong bối cảnh nghề nghiệp của {chuyen_nganh}?',
            'Bạn thích làm việc với đồng nghiệp / đối tượng khách hàng gắn với {chuyen_nganh}?',
            'Bạn phù hợp với điều kiện, áp lực và tính chất công việc của ngành {nganh}?',
            'Bạn hình dung môi trường nghề nghiệp của {chuyen_nganh} phù hợp với mình?',
        ],
        'PCLV' => [
            'Phong cách làm việc của bạn phù hợp với cách thức làm việc trong {chuyen_nganh}?',
            'Bạn thích cách tổ chức công việc, lập kế hoạch điển hình của ngành {nganh}?',
            'Bạn làm việc hiệu quả theo quy trình và thói quen nghề nghiệp của {chuyen_nganh}?',
            'Bạn thích kiểu phối hợp nhóm / làm việc độc lập thường gặp ở {chuyen_nganh}?',
            'Bạn phù hợp với nhịp độ ra quyết định và giải quyết vấn đề trong {nganh}?',
            'Cách bạn ưu tiên nhiệm vụ khớp với yêu cầu công việc của {chuyen_nganh}?',
        ],
        '_default' => [
            'Bạn đánh giá mức độ phù hợp của mình với chuyên ngành {chuyen_nganh} (ngành {nganh})?',
            'Bạn quan tâm đến định hướng nghề nghiệp liên quan đến {chuyen_nganh}?',
            'Bạn sẵn sàng theo học và phát triển trong lĩnh vực {nganh} — {chuyen_nganh}?',
            'Bạn cảm thấy bản thân phù hợp với nhóm công việc thuộc {chuyen_nganh}?',
            'Bạn muốn tìm hiểu thêm cơ hội nghề nghiệp trong ngành {nganh}?',
            'Bạn tự tin chọn {chuyen_nganh} là một hướng đi nghề nghiệp phù hợp?',
        ],
    ];

    public function up(): void
    {
        $loaiList = DB::table('danh_muc_loai_cau_hoi')
            ->orderBy('id')
            ->get(['id', 'ma_loai_cau_hoi', 'ten_loai_cau_hoi']);

        $chuyenNganhList = DB::table('danh_muc_chuyen_nganh as cn')
            ->join('danh_muc_nganh_hoc as nh', 'nh.id', '=', 'cn.nganh_hoc_id')
            ->orderBy('cn.id')
            ->get([
                'cn.id as chuyen_nganh_id',
                'cn.nganh_hoc_id',
                'cn.ten_chuyen_nganh',
                'nh.ten_nganh',
            ]);

        if ($loaiList->isEmpty() || $chuyenNganhList->isEmpty()) {
            return;
        }

        $now = now();
        $chuyenCount = $chuyenNganhList->count();

        foreach ($loaiList as $loai) {
            $templates = self::QUESTION_TEMPLATES[$loai->ma_loai_cau_hoi]
                ?? self::QUESTION_TEMPLATES['_default'];
            $templateCount = count($templates);

            $questionRows = [];

            for ($i = 0; $i < self::QUESTIONS_PER_LOAI; $i++) {
                $cn = $chuyenNganhList[$i % $chuyenCount];
                $template = $templates[$i % $templateCount];
                $stt = intdiv($i, $chuyenCount) + 1;

                $noiDung = strtr($template, [
                    '{nganh}' => $cn->ten_nganh,
                    '{chuyen_nganh}' => $cn->ten_chuyen_nganh,
                    '{stt}' => (string) $stt,
                ]);

                // Phân biệt các vòng lặp khi cùng template + cùng chuyên ngành
                if ($stt > 1) {
                    $noiDung .= " (Góc nhìn {$stt})";
                }

                $questionRows[] = [
                    'nganh_hoc_id' => $cn->nganh_hoc_id,
                    'chuyen_nganh_id' => $cn->chuyen_nganh_id,
                    'loai_cau_hoi_id' => $loai->id,
                    'noi_dung_cau_hoi' => $noiDung,
                    'ghi_chu' => self::SEED_MARKER,
                    'trang_thai' => TrangThaiTracNghiemCauHoi::DangSuDung->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('trac_nghiem_cau_hoi')->insert($questionRows);

            $insertedIds = DB::table('trac_nghiem_cau_hoi')
                ->where('loai_cau_hoi_id', $loai->id)
                ->where('ghi_chu', self::SEED_MARKER)
                ->orderBy('id')
                ->pluck('id');

            $answerRows = [];
            foreach ($insertedIds as $cauHoiId) {
                foreach (self::ANSWERS as $answer) {
                    $answerRows[] = [
                        'cau_hoi_id' => $cauHoiId,
                        'noi_dung_cau_tra_loi' => $answer['noi_dung'],
                        'diem' => $answer['diem'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($answerRows, 500) as $chunk) {
                DB::table('trac_nghiem_cau_tra_loi')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        $questionIds = DB::table('trac_nghiem_cau_hoi')
            ->where('ghi_chu', self::SEED_MARKER)
            ->pluck('id');

        if ($questionIds->isEmpty()) {
            return;
        }

        DB::table('trac_nghiem_cau_tra_loi')
            ->whereIn('cau_hoi_id', $questionIds)
            ->delete();

        DB::table('trac_nghiem_cau_hoi')
            ->whereIn('id', $questionIds)
            ->delete();
    }
};
