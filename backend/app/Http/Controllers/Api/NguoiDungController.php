<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
    /**
     * Lấy hồ sơ khảo sát theo user đang đăng nhập.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem hồ sơ khảo sát.');
            }

            $profile = NguoiDung::query()
                ->with('user')
                ->where('user_id', $user->id)
                ->first();

            if (! $profile) {
                return ApiResponse::success(null, 'Chưa có hồ sơ khảo sát cá nhân.');
            }

            return ApiResponse::success(
                $this->toPublicArray($profile),
                'Lấy thông tin cá nhân thành công.',
            );
        });
    }

    /**
     * Lưu thông tin: tạo/cập nhật users trước, sau đó upsert thong_tin_nguoi_dung theo user_id.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $emailInput = strtolower(trim((string) $request->input('email', '')));
            // Route public: lấy user từ token Sanctum nếu có (không bắt buộc đăng nhập).
            $authUser = $request->user('sanctum') ?? $request->user();

            $existingUser = null;
            if ($authUser && strtolower(trim((string) $authUser->email)) === $emailInput) {
                $existingUser = $authUser;
            } elseif ($emailInput !== '') {
                $existingUser = User::query()
                    ->whereRaw('LOWER(email) = ?', [$emailInput])
                    ->first();
            }

            $validated = $request->validate([
                'ho_ten' => ['required', 'string', 'max:255'],
                'ngay_sinh' => NguoiDung::ngaySinhRules(),
                'gioi_tinh' => ['nullable', 'string', 'max:20'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($existingUser?->id),
                ],
                'so_dien_thoai' => ['nullable', 'string', 'max:30'],
                'mat_khau' => [
                    Rule::requiredIf(! $existingUser),
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
            $hoTen = trim($validated['ho_ten']);
            $soDienThoai = $validated['so_dien_thoai'] ?? null;

            [$profile, $httpStatus, $message] = DB::transaction(function () use (
                $existingUser,
                $validated,
                $email,
                $hoTen,
                $soDienThoai,
            ) {
                if ($existingUser) {
                    $existingUser->fill([
                        'name' => $hoTen,
                        'so_dien_thoai' => $soDienThoai,
                    ]);
                    if (! empty($validated['mat_khau'])) {
                        $existingUser->password = $validated['mat_khau'];
                    }
                    $existingUser->save();
                    $user = $existingUser->fresh();
                } else {
                    $user = User::query()->create([
                        'name' => $hoTen,
                        'email' => $email,
                        'so_dien_thoai' => $soDienThoai,
                        'password' => $validated['mat_khau'],
                        'role' => UserRole::User,
                        'trang_thai' => TrangThaiUser::DangHoatDong,
                    ]);
                }

                $profilePayload = [
                    'ngay_sinh' => NguoiDung::normalizeNgaySinh($validated['ngay_sinh'] ?? null),
                    'gioi_tinh' => $validated['gioi_tinh'] ?? null,
                    'dan_toc' => $validated['dan_toc'] ?? null,
                    'ton_giao' => $validated['ton_giao'] ?? null,
                    'trinh_do_hoc_van' => $validated['trinh_do_hoc_van'] ?? null,
                    'suc_khoe_the_chat' => $validated['suc_khoe_the_chat'] ?? null,
                    'kha_nang_tai_chinh' => $validated['kha_nang_tai_chinh'] ?? null,
                    'vi_tri_dia_ly' => $validated['vi_tri_dia_ly'] ?? null,
                ];

                $existingProfile = NguoiDung::query()
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingProfile) {
                    $existingProfile->fill($profilePayload);
                    $existingProfile->save();
                    $profile = $existingProfile->fresh(['user']);

                    return [$profile, 200, 'Cập nhật thông tin cá nhân thành công.'];
                }

                $profile = NguoiDung::query()->create([
                    'user_id' => $user->id,
                    ...$profilePayload,
                ])->load('user');

                return [$profile, 201, 'Lưu thông tin cá nhân thành công.'];
            });

            return ApiResponse::success(
                $this->toPublicArray($profile),
                $message,
                httpStatus: $httpStatus,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(NguoiDung $profile): array
    {
        $user = $profile->relationLoaded('user') ? $profile->user : $profile->user()->first();

        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'ho_ten' => $user?->name,
            'email' => $user?->email,
            'ngay_sinh' => $profile->ngay_sinh,
            'gioi_tinh' => $profile->gioi_tinh,
            'so_dien_thoai' => $user?->so_dien_thoai,
            'dan_toc' => $profile->dan_toc,
            'ton_giao' => $profile->ton_giao,
            'trinh_do_hoc_van' => $profile->trinh_do_hoc_van,
            'suc_khoe_the_chat' => $profile->suc_khoe_the_chat,
            'kha_nang_tai_chinh' => $profile->kha_nang_tai_chinh,
            'vi_tri_dia_ly' => $profile->vi_tri_dia_ly,
            'edu_coin' => (int) ($profile->edu_coin ?? 0),
            'xu_he_thong' => (int) ($profile->xu_he_thong ?? 0),
        ];
    }
}
