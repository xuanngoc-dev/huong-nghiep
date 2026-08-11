<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
    /**
     * Lấy hồ sơ khảo sát theo email của user đang đăng nhập.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user?->email) {
                return ApiResponse::error('Bạn cần đăng nhập để xem hồ sơ khảo sát.');
            }

            $nguoiDung = NguoiDung::query()
                ->where('email', strtolower(trim($user->email)))
                ->first();

            if (! $nguoiDung) {
                return ApiResponse::success(null, 'Chưa có hồ sơ khảo sát cá nhân.');
            }

            return ApiResponse::success(
                $this->toPublicArray($nguoiDung),
                'Lấy thông tin cá nhân thành công.',
            );
        });
    }

    /**
     * Lưu / cập nhật thông tin khảo sát cá nhân (bảng nguoi_dung).
     * Trùng email → cập nhật bản ghi hiện có (không bắt buộc mật khẩu).
     */
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $emailInput = strtolower(trim((string) $request->input('email', '')));
            $existing = $emailInput !== ''
                ? NguoiDung::query()->where('email', $emailInput)->first()
                : null;

            $validated = $request->validate([
                'ho_ten' => ['required', 'string', 'max:255'],
                'ngay_sinh' => ['nullable', 'date', 'before_or_equal:today'],
                'gioi_tinh' => ['nullable', 'string', 'max:20'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'so_dien_thoai' => ['nullable', 'string', 'max:30'],
                'mat_khau' => [
                    Rule::requiredIf(! $existing),
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'dan_toc' => ['nullable', 'string', 'max:100'],
                'ton_giao' => ['nullable', 'string', 'max:100'],
                'trinh_do_hoc_van' => ['nullable', 'array'],
                'trinh_do_hoc_van.trinh_do_hoc_van' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'tot_nghiep_thpt',
                        'tot_nghiep_thcs',
                        'dang_hoc_thpt',
                        'trung_cap',
                        'cao_dang',
                        'dai_hoc',
                        'khac',
                    ]),
                ],
                'trinh_do_hoc_van.trinh_do_khac' => ['nullable', 'string', 'max:255'],
                'trinh_do_hoc_van.chung_chi_tieng_anh' => ['nullable', 'array'],
                'trinh_do_hoc_van.chung_chi_tieng_anh.*.ten_chung_chi' => ['nullable', 'string', 'max:100'],
                'trinh_do_hoc_van.chung_chi_tieng_anh.*.diem_chung_chi' => ['nullable', 'string', 'max:50'],
                'trinh_do_hoc_van.diem_trung_binh_to_hop_mon' => ['nullable', 'array'],
                'trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemHocBa' => ['nullable', 'numeric', 'min:0', 'max:10'],
                'trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemThiTHPT' => ['nullable', 'numeric', 'min:0', 'max:30'],
                'suc_khoe_the_chat' => ['nullable', 'array'],
                'suc_khoe_the_chat.chieu_cao' => ['nullable', 'numeric', 'min:0', 'max:250'],
                'suc_khoe_the_chat.can_nang' => ['nullable', 'numeric', 'min:0', 'max:300'],
                'suc_khoe_the_chat.benh_ly' => ['nullable', 'string', 'max:500'],
                'kha_nang_tai_chinh' => ['nullable', 'array'],
                'kha_nang_tai_chinh.chi_tra_mot_nam_hoc' => ['nullable', 'numeric', 'min:0'],
                'vi_tri_dia_ly' => ['nullable', 'array'],
                'vi_tri_dia_ly.khu_vuc_muon_theo_hoc' => [
                    'nullable',
                    'string',
                    Rule::in(['bac', 'trung', 'nam']),
                ],
                'vi_tri_dia_ly.tinh_thanh_muon_theo_hoc' => ['nullable', 'string', 'max:50'],
                'vi_tri_dia_ly.tinh_thanh_dang_song' => ['nullable', 'string', 'max:50'],
            ]);

            $email = strtolower(trim($validated['email']));

            $payload = [
                'ho_ten' => trim($validated['ho_ten']),
                'ngay_sinh' => $validated['ngay_sinh'] ?? null,
                'gioi_tinh' => $validated['gioi_tinh'] ?? null,
                'email' => $email,
                'so_dien_thoai' => $validated['so_dien_thoai'] ?? null,
                'dan_toc' => $validated['dan_toc'] ?? null,
                'ton_giao' => $validated['ton_giao'] ?? null,
                'trinh_do_hoc_van' => $validated['trinh_do_hoc_van'] ?? null,
                'suc_khoe_the_chat' => $validated['suc_khoe_the_chat'] ?? null,
                'kha_nang_tai_chinh' => $validated['kha_nang_tai_chinh'] ?? null,
                'vi_tri_dia_ly' => $validated['vi_tri_dia_ly'] ?? null,
            ];

            if (! empty($validated['mat_khau'])) {
                $payload['mat_khau'] = $validated['mat_khau'];
            }

            if ($existing) {
                $existing->fill($payload);
                $existing->save();
                $nguoiDung = $existing->fresh();
                $message = 'Cập nhật thông tin cá nhân thành công.';
                $httpStatus = 200;
            } else {
                $nguoiDung = NguoiDung::query()->create($payload);
                $message = 'Lưu thông tin cá nhân thành công.';
                $httpStatus = 201;
            }

            return ApiResponse::success(
                $this->toPublicArray($nguoiDung),
                $message,
                httpStatus: $httpStatus,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(NguoiDung $nguoiDung): array
    {
        return [
            'id' => $nguoiDung->id,
            'ho_ten' => $nguoiDung->ho_ten,
            'email' => $nguoiDung->email,
            'ngay_sinh' => $nguoiDung->ngay_sinh?->format('Y-m-d'),
            'gioi_tinh' => $nguoiDung->gioi_tinh,
            'so_dien_thoai' => $nguoiDung->so_dien_thoai,
            'dan_toc' => $nguoiDung->dan_toc,
            'ton_giao' => $nguoiDung->ton_giao,
            'trinh_do_hoc_van' => $nguoiDung->trinh_do_hoc_van,
            'suc_khoe_the_chat' => $nguoiDung->suc_khoe_the_chat,
            'kha_nang_tai_chinh' => $nguoiDung->kha_nang_tai_chinh,
            'vi_tri_dia_ly' => $nguoiDung->vi_tri_dia_ly,
        ];
    }
}
