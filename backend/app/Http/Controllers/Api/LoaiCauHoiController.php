<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Http\Controllers\Controller;
use App\Models\LoaiCauHoi;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LoaiCauHoiController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->tryApi(function () {
            $items = LoaiCauHoi::query()
                ->where('trang_thai', TrangThaiLoaiCauHoi::DangSuDung)
                ->orderBy('thu_tu_uu_tien')
                ->orderBy('ten_loai_cau_hoi')
                ->get([
                    'id',
                    'ma_loai_cau_hoi',
                    'ten_loai_cau_hoi',
                    'thu_tu_uu_tien',
                    'ghi_chu',
                ]);

            return ApiResponse::success($items, 'Lấy danh sách loại câu hỏi thành công.');
        });
    }
}
