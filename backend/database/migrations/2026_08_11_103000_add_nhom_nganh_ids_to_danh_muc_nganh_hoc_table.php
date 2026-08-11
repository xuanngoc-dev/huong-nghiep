<?php

use App\Enums\TrangThaiNhomNganh;
use App\Models\NganhHoc;
use App\Models\NhomNganh;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('danh_muc_nganh_hoc', function (Blueprint $table) {
            $table->json('nhom_nganh_ids')->nullable()->after('ghi_chu');
        });

        $this->backfillNhomNganhIds();
    }

    public function down(): void
    {
        Schema::table('danh_muc_nganh_hoc', function (Blueprint $table) {
            $table->dropColumn('nhom_nganh_ids');
        });
    }

    private function backfillNhomNganhIds(): void
    {
        $nhomByTen = NhomNganh::query()
            ->where('trang_thai', TrangThaiNhomNganh::DangSuDung)
            ->get(['id', 'ten_nhom_nganh'])
            ->keyBy('ten_nhom_nganh');

        if ($nhomByTen->isEmpty()) {
            $nhomByTen = NhomNganh::query()
                ->get(['id', 'ten_nhom_nganh'])
                ->keyBy('ten_nhom_nganh');
        }

        $idKyThuat = $this->resolveNhomId($nhomByTen, [
            'Kỹ thuật & Công nghệ (Engineering & Technology)',
            'Kỹ thuật & Công nghệ',
        ]);
        $idKhoaHoc = $this->resolveNhomId($nhomByTen, [
            'Khoa học & Nghiên cứu (Science & Research)',
            'Khoa học & Nghiên cứu',
        ]);
        $idGiaoDuc = $this->resolveNhomId($nhomByTen, [
            'Giáo dục & Công tác xã hội (Education & Social Work)',
            'Giáo dục & Công tác xã hội',
        ]);
        $idKinhDoanh = $this->resolveNhomId($nhomByTen, [
            'Kinh doanh & quản lý',
            'Kinh doanh & Quản lý',
        ]);
        $idNgheThuat = $this->resolveNhomId($nhomByTen, [
            'Nghệ thuật & Sáng tạo (Arts & Creativity)',
            'Nghệ thuật & Sáng tạo',
        ]);
        $idQuanSu = $this->resolveNhomId($nhomByTen, [
            'Quân đội, Công an, Hàng không (Military, Police & Aviation)',
            'Quân đội, Công an, Hàng không',
        ]);

        /** @var array<string, list<int|null>> $mapping */
        $mapping = [
            'CNTT' => [$idKyThuat, $idKhoaHoc],
            'KTPM' => [$idKyThuat],
            'KHMT' => [$idKyThuat, $idKhoaHoc],
            'ATTT' => [$idKyThuat],
            'HTTT' => [$idKyThuat, $idKinhDoanh],
            'TTNT' => [$idKyThuat, $idKhoaHoc],
            'KHDL' => [$idKyThuat, $idKhoaHoc],
            'MMT' => [$idKyThuat],
            'TKDH' => [$idNgheThuat],
            'TKSO' => [$idNgheThuat, $idKyThuat],
            'QTKD' => [$idKinhDoanh],
            'KT' => [$idKinhDoanh],
            'TCNH' => [$idKinhDoanh],
            'MK' => [$idKinhDoanh, $idNgheThuat],
            'TMĐT' => [$idKinhDoanh, $idKyThuat],
            'QTNL' => [$idKinhDoanh, $idGiaoDuc],
            'DL' => [$idKinhDoanh],
            'NHKS' => [$idKinhDoanh],
            'NNAnh' => [$idGiaoDuc],
            'NNNhat' => [$idGiaoDuc],
            'NNHan' => [$idGiaoDuc],
            'TT' => [$idNgheThuat],
            'BC' => [$idNgheThuat, $idGiaoDuc],
            'XD' => [$idKyThuat],
            'KTOT' => [$idKyThuat],
            'DTVT' => [$idKyThuat],
            'CK' => [$idKyThuat],
            'LOG' => [$idKinhDoanh, $idKyThuat],
            'LUAT' => [$idGiaoDuc, $idQuanSu],
            'YKH' => [$idKhoaHoc],
        ];

        NganhHoc::query()->orderBy('id')->each(function (NganhHoc $nganh) use ($mapping) {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', $mapping[$nganh->ma_nganh] ?? []),
                fn (int $id) => $id > 0,
            )));

            // Fallback: ngành chưa map thì gán nhóm kỹ thuật nếu có, không thì mảng rỗng
            if ($ids === [] && isset($mapping[$nganh->ma_nganh]) === false) {
                $fallback = array_values(array_filter(array_map(
                    'intval',
                    [$mapping['CNTT'][0] ?? null],
                )));
                $ids = $fallback;
            }

            $nganh->forceFill(['nhom_nganh_ids' => $ids])->save();
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<string, NhomNganh>  $nhomByTen
     * @param  list<string>  $candidates
     */
    private function resolveNhomId($nhomByTen, array $candidates): ?int
    {
        foreach ($candidates as $ten) {
            if ($nhomByTen->has($ten)) {
                return (int) $nhomByTen->get($ten)->id;
            }
        }

        foreach ($nhomByTen as $ten => $nhom) {
            foreach ($candidates as $candidate) {
                if (str_contains((string) $ten, explode(' (', $candidate)[0])) {
                    return (int) $nhom->id;
                }
            }
        }

        return null;
    }
};
