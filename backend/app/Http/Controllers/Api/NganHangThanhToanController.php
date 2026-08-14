<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiNganHangThanhToan;
use App\Http\Controllers\Controller;
use App\Models\NapEduCoin;
use App\Models\NganHangThanhToan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class NganHangThanhToanController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->tryApi(function () {
            $items = NganHangThanhToan::query()
                ->where('trang_thai', TrangThaiNganHangThanhToan::DangSuDung)
                ->orderBy('ten_ngan_hang')
                ->get([
                    'id',
                    'ten_ngan_hang',
                    'ten_viet_tat',
                    'hinh_anh_logo',
                    'so_tai_khoan',
                    'chu_tai_khoan',
                    'chi_nhanh',
                ]);

            return ApiResponse::success(
                $items,
                'Lấy danh sách ngân hàng thanh toán thành công.',
                ['ty_gia' => NapEduCoin::TY_GIA],
            );
        });
    }
}
