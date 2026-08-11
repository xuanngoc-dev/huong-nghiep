<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Enums\TrangThaiTracNghiemCauHoi;
use App\Http\Controllers\Controller;
use App\Models\LoaiCauHoi;
use App\Models\TracNghiemCauHoi;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TracNghiemCauHoiController extends Controller
{
    /**
     * Lấy N câu hỏi ngẫu nhiên theo loại (kèm đáp án) để làm trắc nghiệm.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ma_loai_cau_hoi' => ['required', 'string', 'max:50'],
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $ma = strtolower(trim($validated['ma_loai_cau_hoi']));
            $limit = (int) ($validated['limit'] ?? 10);

            $loai = LoaiCauHoi::query()
                ->whereRaw('LOWER(ma_loai_cau_hoi) = ?', [$ma])
                ->where('trang_thai', TrangThaiLoaiCauHoi::DangSuDung)
                ->first(['id', 'ma_loai_cau_hoi', 'ten_loai_cau_hoi']);

            if (!$loai) {
                return ApiResponse::error('Không tìm thấy loại câu hỏi.');
            }

            $items = TracNghiemCauHoi::query()
                ->where('loai_cau_hoi_id', $loai->id)
                ->where('trang_thai', TrangThaiTracNghiemCauHoi::DangSuDung)
                ->with([
                    'cauTraLois' => fn ($q) => $q
                        ->orderBy('diem')
                        ->orderBy('id')
                        ->select(['id', 'cau_hoi_id', 'noi_dung_cau_tra_loi', 'diem']),
                ])
                ->inRandomOrder()
                ->limit($limit)
                ->get([
                    'id',
                    'loai_cau_hoi_id',
                    'nhom_nganh_id',
                    'noi_dung_cau_hoi',
                ]);

            return ApiResponse::success(
                [
                    'loai_cau_hoi' => $loai,
                    'questions' => $items,
                    'limit' => $limit,
                ],
                'Lấy câu hỏi trắc nghiệm thành công.',
            );
        });
    }
}
