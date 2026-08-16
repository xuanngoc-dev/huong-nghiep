<?php

namespace App\Http\Controllers\Api;

use App\Enums\KenhThanhToan;
use App\Enums\TrangThaiNganHangThanhToan;
use App\Enums\TrangThaiYeuCauNapEduCoin;
use App\Http\Controllers\Controller;
use App\Models\NapEduCoin;
use App\Models\NganHangThanhToan;
use App\Support\ApiResponse;
use App\Support\MaGiaoDich;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NapEduCoinController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để nạp Edu Coin.');
            }

            $validated = $request->validate([
                'so_luong_edu_coin' => [
                    'required',
                    'integer',
                    'min:'.NapEduCoin::SO_LUONG_MIN,
                    'max:'.NapEduCoin::SO_LUONG_MAX,
                ],
                'ngan_hang_thanh_toan_id' => [
                    'required',
                    'integer',
                    'exists:he_thong_ngan_hang_thanh_toan,id',
                ],
                'ma_giao_dich' => [
                    'nullable',
                    'string',
                    'regex:/^NAP[A-Za-z0-9]{8}ECOIN$/i',
                ],
                'ghi_chu' => ['nullable', 'string', 'max:255'],
            ], [
                'so_luong_edu_coin.required' => 'Vui lòng nhập số Edu Coin cần nạp.',
                'so_luong_edu_coin.integer' => 'Số Edu Coin phải là số nguyên.',
                'so_luong_edu_coin.min' => 'Số Edu Coin phải từ 1 đến 10.000.',
                'so_luong_edu_coin.max' => 'Số Edu Coin phải từ 1 đến 10.000.',
                'ngan_hang_thanh_toan_id.required' => 'Vui lòng chọn ngân hàng nhận chuyển khoản.',
                'ngan_hang_thanh_toan_id.exists' => 'Ngân hàng không tồn tại.',
                'ma_giao_dich.regex' => 'Mã giao dịch nạp không đúng định dạng.',
            ]);

            $bank = NganHangThanhToan::query()
                ->where('id', $validated['ngan_hang_thanh_toan_id'])
                ->where('trang_thai', TrangThaiNganHangThanhToan::DangSuDung)
                ->first();

            if ($bank === null) {
                return ApiResponse::error('Ngân hàng không khả dụng để nạp coin.');
            }

            $soLuong = (int) $validated['so_luong_edu_coin'];
            $soTienNap = $soLuong * NapEduCoin::TY_GIA;
            $maGiaoDich = MaGiaoDich::resolveMaNap($validated['ma_giao_dich'] ?? null);
            if ($maGiaoDich === null) {
                return ApiResponse::error('Mã giao dịch đã tồn tại. Vui lòng tạo lại yêu cầu nạp.');
            }

            $item = NapEduCoin::query()->create([
                'nguoi_nap_id' => $user->id,
                'nguoi_duyet_id' => null,
                'ma_giao_dich' => $maGiaoDich,
                'so_luong_edu_coin' => $soLuong,
                'so_tien_nap' => $soTienNap,
                'kenh_thanh_toan' => KenhThanhToan::ChuyenKhoan,
                'thong_tin_thanh_toan' => $this->buildThongTinThanhToan($bank, $maGiaoDich),
                'trang_thai' => TrangThaiYeuCauNapEduCoin::ChoDuyet,
                'ghi_chu' => $validated['ghi_chu'] ?? null,
            ]);

            return ApiResponse::success(
                $this->toPublicArray($item),
                'Đã gửi yêu cầu nạp Edu Coin. Vui lòng chuyển khoản và chờ duyệt.',
            );
        });
    }

    public function show(Request $request, NapEduCoin $napEduCoin): JsonResponse
    {
        return $this->tryApi(function () use ($request, $napEduCoin) {
            $user = $request->user();
            if (! $user || (int) $napEduCoin->nguoi_nap_id !== (int) $user->id) {
                return ApiResponse::error('Không tìm thấy yêu cầu nạp.');
            }

            return ApiResponse::success(
                $this->toPublicArray($napEduCoin),
                'Lấy chi tiết yêu cầu nạp Edu Coin thành công.',
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    private function buildThongTinThanhToan(NganHangThanhToan $bank, string $maGiaoDich): array
    {
        return [
            'ngan_hang_thanh_toan_id' => $bank->id,
            'ten_ngan_hang' => $bank->ten_ngan_hang,
            'ten_viet_tat' => $bank->ten_viet_tat,
            'so_tai_khoan' => $bank->so_tai_khoan,
            'chu_tai_khoan' => $bank->chu_tai_khoan,
            'chi_nhanh' => $bank->chi_nhanh,
            'ma_giao_dich' => $maGiaoDich,
            'noi_dung_chuyen_khoan' => $maGiaoDich,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(NapEduCoin $item): array
    {
        $trangThai = $item->trang_thai instanceof TrangThaiYeuCauNapEduCoin
            ? $item->trang_thai
            : TrangThaiYeuCauNapEduCoin::tryFrom((string) $item->trang_thai);

        $kenh = $item->kenh_thanh_toan instanceof KenhThanhToan
            ? $item->kenh_thanh_toan
            : KenhThanhToan::tryFrom((string) $item->kenh_thanh_toan);

        return [
            'id' => $item->id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'so_luong_edu_coin' => (int) $item->so_luong_edu_coin,
            'so_tien_nap' => (int) $item->so_tien_nap,
            'kenh_thanh_toan' => $kenh?->value,
            'kenh_thanh_toan_label' => $kenh?->label(),
            'thong_tin_thanh_toan' => $item->thong_tin_thanh_toan,
            'trang_thai' => $trangThai?->value ?? TrangThaiYeuCauNapEduCoin::ChoDuyet->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiYeuCauNapEduCoin::ChoDuyet->label(),
            'ghi_chu' => $item->ghi_chu,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }
}
