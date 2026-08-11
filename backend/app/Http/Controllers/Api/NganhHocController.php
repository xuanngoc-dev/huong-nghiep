<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiNganhHoc;
use App\Http\Controllers\Controller;
use App\Models\NganhHoc;
use App\Models\TruongHocTuyenSinhTheoNam;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NganhHocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $nhomNganhId = $request->query('nhom_nganh_id');

            if ($nhomNganhId === null || $nhomNganhId === '') {
                throw ValidationException::withMessages([
                    'nhom_nganh_id' => ['Vui lòng chọn nhóm ngành.'],
                ]);
            }

            $id = (int) $nhomNganhId;

            $items = NganhHoc::query()
                ->where('trang_thai', TrangThaiNganhHoc::DangSuDung)
                ->where(function ($query) use ($id) {
                    $query->whereJsonContains('nhom_nganh_ids', $id)
                        ->orWhereJsonContains('nhom_nganh_ids', (string) $id);
                })
                ->orderBy('ten_nganh')
                ->get(['id', 'ten_nganh', 'ma_nganh', 'ghi_chu', 'nhom_nganh_ids']);

            return ApiResponse::success($items, 'Lấy danh sách ngành học thành công.');
        });
    }

    public function truongTuyenSinh(Request $request, NganhHoc $nganhHoc): JsonResponse
    {
        return $this->tryApi(function () use ($request, $nganhHoc) {
            $namTuyenSinh = (int) $request->query('nam_tuyen_sinh', now()->year - 1);

            if ($namTuyenSinh < 2000 || $namTuyenSinh > 2100) {
                throw ValidationException::withMessages([
                    'nam_tuyen_sinh' => ['Năm tuyển sinh không hợp lệ.'],
                ]);
            }

            $namDiemChuan = [
                $namTuyenSinh - 2,
                $namTuyenSinh - 1,
                $namTuyenSinh,
            ];

            $records = TruongHocTuyenSinhTheoNam::query()
                ->with([
                    'truongHoc:id,ma_truong,ten_truong',
                    'nganhHocTuyenSinh:id,ma_nganh,ten_nganh',
                    'chuyenNganhTuyenSinh:id,ma_chuyen_nganh,ten_chuyen_nganh,nganh_hoc_id',
                ])
                ->where('nganh_hoc_tuyen_sinh_id', $nganhHoc->id)
                ->where(function ($query) use ($namDiemChuan) {
                    foreach ($namDiemChuan as $year) {
                        $query->orWhere('nam_hoc', 'like', $year.'%');
                    }
                })
                ->orderBy('ma_truong')
                ->orderBy('nam_hoc')
                ->get();

            $grouped = [];

            foreach ($records as $record) {
                $year = $this->extractStartYear((string) $record->nam_hoc);
                if ($year === null || ! in_array($year, $namDiemChuan, true)) {
                    continue;
                }

                $chuyenId = $record->chuyen_nganh_tuyen_sinh_id;
                $groupKey = $record->ma_truong.'|'.($chuyenId ?? 'null');

                if (! isset($grouped[$groupKey])) {
                    $tenNganhDaoTao = $record->chuyenNganhTuyenSinh?->ten_chuyen_nganh
                        ?: ($record->nganhHocTuyenSinh?->ten_nganh ?: $nganhHoc->ten_nganh);

                    $grouped[$groupKey] = [
                        'ma_truong' => $record->ma_truong,
                        'ten_truong' => $record->truongHoc?->ten_truong ?: $record->ma_truong,
                        'ten_nganh_dao_tao' => $tenNganhDaoTao,
                        'chuyen_nganh_tuyen_sinh_id' => $chuyenId,
                        'chi_tieu' => null,
                        'phuong_thuc_xet_tuyen' => null,
                        'diem_chuan' => array_fill_keys(array_map('strval', $namDiemChuan), null),
                        'has_nam_tuyen_sinh' => false,
                        'nam_tuyen_sinh' => $namTuyenSinh,
                        'nam_diem_chuan' => $namDiemChuan,
                    ];
                }

                $grouped[$groupKey]['diem_chuan'][(string) $year] = $record->diem_chuan;

                if ($year === $namTuyenSinh) {
                    $grouped[$groupKey]['has_nam_tuyen_sinh'] = true;
                    $grouped[$groupKey]['chi_tieu'] = $record->chi_tieu;
                    $grouped[$groupKey]['phuong_thuc_xet_tuyen'] = $record->phuong_thuc_xet_tuyen;
                    $grouped[$groupKey]['ten_truong'] = $record->truongHoc?->ten_truong
                        ?: $grouped[$groupKey]['ten_truong'];
                    $grouped[$groupKey]['ten_nganh_dao_tao'] = $record->chuyenNganhTuyenSinh?->ten_chuyen_nganh
                        ?: ($record->nganhHocTuyenSinh?->ten_nganh ?: $grouped[$groupKey]['ten_nganh_dao_tao']);
                }
            }

            $rows = collect($grouped)
                ->filter(fn (array $row) => $row['has_nam_tuyen_sinh'] === true)
                ->map(function (array $row) {
                    unset($row['has_nam_tuyen_sinh']);

                    return $row;
                })
                ->sortBy([
                    ['ten_truong', 'asc'],
                    ['ten_nganh_dao_tao', 'asc'],
                ])
                ->values()
                ->all();

            return ApiResponse::success([
                'nganh_hoc' => [
                    'id' => $nganhHoc->id,
                    'ma_nganh' => $nganhHoc->ma_nganh,
                    'ten_nganh' => $nganhHoc->ten_nganh,
                ],
                'nam_tuyen_sinh' => $namTuyenSinh,
                'nam_diem_chuan' => $namDiemChuan,
                'items' => $rows,
            ], 'Lấy danh sách trường tuyển sinh theo ngành thành công.');
        });
    }

    private function extractStartYear(string $namHoc): ?int
    {
        if (preg_match('/^(\d{4})/', trim($namHoc), $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
