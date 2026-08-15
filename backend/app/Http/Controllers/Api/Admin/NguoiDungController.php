<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\KenhThanhToan;
use App\Enums\LoaiKhuyenMai;
use App\Enums\LoaiNapTien;
use App\Enums\TrangThaiNapEduCoin;
use App\Enums\TrangThaiUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\LichSuNapEduCoin;
use App\Models\NganHangThanhToan;
use App\Models\NguoiDung;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
    /** 1 Edu Coin = 1000 VND */
    private const EDU_COIN_RATE = 1000;

    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $gioiTinh = trim((string) $request->query('gioi_tinh', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = User::query()
                ->where('role', UserRole::User)
                ->with('thongTinNguoiDung')
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiUser::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->when($gioiTinh !== '', function ($query) use ($gioiTinh) {
                    $query->whereHas(
                        'thongTinNguoiDung',
                        fn ($profileQuery) => $profileQuery->where('gioi_tinh', $gioiTinh),
                    );
                })
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (User $item) => $this->toPublicArray($item))->values(),
                'Lấy danh sách người dùng thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(User $user): JsonResponse
    {
        return $this->tryApi(function () use ($user) {
            abort_unless($user->role === UserRole::User, 404);

            $user->loadMissing('thongTinNguoiDung');

            return ApiResponse::success(
                $this->toPublicArray($user),
                'Lấy chi tiết người dùng thành công.',
            );
        });
    }

    public function update(Request $request, User $user): JsonResponse
    {
        return $this->tryApi(function () use ($request, $user) {
            abort_unless($user->role === UserRole::User, 404);

            $validated = $request->validate([
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiUser::class)],
            ]);

            $user->update($validated);
            $user->loadMissing('thongTinNguoiDung');

            return ApiResponse::success(
                $this->toPublicArray($user->fresh(['thongTinNguoiDung'])),
                'Cập nhật người dùng thành công.',
            );
        });
    }

    public function destroy(User $user): JsonResponse
    {
        return $this->tryApi(function () use ($user) {
            abort_unless($user->role === UserRole::User, 404);

            $user->delete();

            return ApiResponse::success(null, 'Đã xóa người dùng.');
        });
    }

    public function changePassword(Request $request, User $user): JsonResponse
    {
        return $this->tryApi(function () use ($request, $user) {
            abort_unless($user->role === UserRole::User, 404);

            $validated = $request->validate([
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    'min:8',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $password = (string) $value;

                        if (! preg_match('/[a-z]/', $password)) {
                            $fail('Mật khẩu phải có ít nhất một chữ thường.');
                        }
                        if (! preg_match('/[A-Z]/', $password)) {
                            $fail('Mật khẩu phải có ít nhất một chữ hoa.');
                        }
                        if (! preg_match('/\d/', $password)) {
                            $fail('Mật khẩu phải có ít nhất một chữ số.');
                        }
                        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
                            $fail('Mật khẩu phải có ít nhất một ký tự đặc biệt.');
                        }
                    },
                ],
            ], [
                'password.required' => 'Vui lòng nhập mật khẩu mới.',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
                'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            ]);

            $user->update([
                'password' => $validated['password'],
            ]);

            $user->tokens()->delete();

            return ApiResponse::success(null, 'Đổi mật khẩu thành công.');
        });
    }

    public function changePaymentPassword(Request $request, User $user): JsonResponse
    {
        return $this->tryApi(function () use ($request, $user) {
            abort_unless($user->role === UserRole::User, 404);

            $validated = $request->validate([
                'mat_khau_thanh_toan' => [
                    'required',
                    'string',
                    'confirmed',
                    'regex:/^\d{6}$/',
                ],
            ], [
                'mat_khau_thanh_toan.required' => 'Vui lòng nhập mật khẩu thanh toán mới.',
                'mat_khau_thanh_toan.confirmed' => 'Mật khẩu thanh toán xác nhận không khớp.',
                'mat_khau_thanh_toan.regex' => 'Mật khẩu thanh toán phải là số gồm đúng 6 chữ số.',
            ]);

            $profile = NguoiDung::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['edu_coin' => 0, 'xu_he_thong' => 0],
            );

            $profile->mat_khau_thanh_toan = $validated['mat_khau_thanh_toan'];
            $profile->save();

            $user->load('thongTinNguoiDung');

            return ApiResponse::success(
                $this->toPublicArray($user),
                'Đổi mật khẩu thanh toán thành công.',
            );
        });
    }

    public function napTien(Request $request, User $user): JsonResponse
    {
        return $this->tryApi(function () use ($request, $user) {
            abort_unless($user->role === UserRole::User, 404);

            $validated = $request->validate([
                'so_coin_nap' => ['required', 'integer', 'min:1'],
                'loai_khuyen_mai' => ['required', Rule::enum(LoaiKhuyenMai::class)],
                'khuyen_mai' => ['required', 'integer', 'min:0'],
                'kenh_thanh_toan' => ['required', Rule::enum(KenhThanhToan::class)],
                'ngan_hang_thanh_toan_id' => [
                    'nullable',
                    'integer',
                    'required_if:kenh_thanh_toan,chuyen_khoan',
                    'exists:he_thong_ngan_hang_thanh_toan,id',
                ],
                'ghi_chu' => ['nullable', 'string', 'max:255'],
            ], [
                'so_coin_nap.required' => 'Vui lòng nhập số coin nạp.',
                'so_coin_nap.integer' => 'Số coin phải là số nguyên.',
                'so_coin_nap.min' => 'Số coin phải ≥ 1.',
                'loai_khuyen_mai.required' => 'Vui lòng chọn loại khuyến mại.',
                'khuyen_mai.required' => 'Vui lòng nhập khuyến mại.',
                'khuyen_mai.integer' => 'Khuyến mại phải là số nguyên.',
                'khuyen_mai.min' => 'Khuyến mại phải ≥ 0.',
                'kenh_thanh_toan.required' => 'Vui lòng chọn kênh thanh toán.',
                'ngan_hang_thanh_toan_id.required_if' => 'Vui lòng chọn ngân hàng nhận chuyển khoản.',
                'ngan_hang_thanh_toan_id.exists' => 'Ngân hàng không tồn tại.',
            ]);

            $loaiKhuyenMai = $validated['loai_khuyen_mai'] instanceof LoaiKhuyenMai
                ? $validated['loai_khuyen_mai']
                : LoaiKhuyenMai::from($validated['loai_khuyen_mai']);
            $kenhThanhToan = $validated['kenh_thanh_toan'] instanceof KenhThanhToan
                ? $validated['kenh_thanh_toan']
                : KenhThanhToan::from($validated['kenh_thanh_toan']);

            $soCoinNap = (int) $validated['so_coin_nap'];
            $khuyenMai = (int) $validated['khuyen_mai'];

            if ($loaiKhuyenMai === LoaiKhuyenMai::PhanTram && $khuyenMai > 1000) {
                return ApiResponse::error('Phần trăm khuyến mại tối đa 1000%.');
            }

            $coinKhuyenMai = $loaiKhuyenMai === LoaiKhuyenMai::PhanTram
                ? intdiv($soCoinNap * $khuyenMai, 100)
                : $khuyenMai;
            $tongCoinNhan = $soCoinNap + $coinKhuyenMai;

            if ($tongCoinNhan < 1) {
                return ApiResponse::error('Tổng coin nhận phải ≥ 1.');
            }

            $actor = $request->user();
            $loaiNapTien = $this->resolveLoaiNapTien($actor);
            $bank = null;
            if ($kenhThanhToan === KenhThanhToan::ChuyenKhoan) {
                $bank = NganHangThanhToan::query()->find($validated['ngan_hang_thanh_toan_id']);
                if ($bank === null) {
                    return ApiResponse::error('Ngân hàng không tồn tại.');
                }
            }

            DB::transaction(function () use (
                $user,
                $actor,
                $validated,
                $soCoinNap,
                $loaiKhuyenMai,
                $coinKhuyenMai,
                $tongCoinNhan,
                $kenhThanhToan,
                $loaiNapTien,
                $bank,
            ) {
                $profile = $user->thongTinNguoiDung()->lockForUpdate()->first();
                if ($profile === null) {
                    $profile = $user->thongTinNguoiDung()->create([
                        'edu_coin' => 0,
                        'xu_he_thong' => 0,
                    ]);
                }

                $soDuTruocNap = (int) $profile->edu_coin;
                $isAdminNap = $loaiNapTien === LoaiNapTien::AdminNap;
                $soDuSauNap = $isAdminNap
                    ? $soDuTruocNap + $tongCoinNhan
                    : $soDuTruocNap;

                LichSuNapEduCoin::query()->create([
                    'nguoi_nap_id' => $user->id,
                    'nguoi_duyet_id' => $isAdminNap ? $actor?->id : null,
                    'nguoi_tao_id' => $actor?->id,
                    'loai_nap_tien' => $loaiNapTien,
                    'so_du_truoc_nap' => $soDuTruocNap,
                    'so_du_sau_nap' => $soDuSauNap,
                    'so_coin_nap' => $soCoinNap,
                    'so_tien_thanh_toan' => $soCoinNap * self::EDU_COIN_RATE,
                    'loai_khuyen_mai' => $loaiKhuyenMai,
                    'coin_khuyen_mai' => $coinKhuyenMai,
                    'tong_coin_nhan' => $tongCoinNhan,
                    'kenh_thanh_toan' => $kenhThanhToan,
                    'thong_tin_thanh_toan' => $this->buildThongTinThanhToan($kenhThanhToan, $bank, $user),
                    'ghi_chu' => $validated['ghi_chu'] ?? null,
                    'trang_thai' => $isAdminNap
                        ? TrangThaiNapEduCoin::DaDuyet
                        : TrangThaiNapEduCoin::DangXuLy,
                ]);

                if ($isAdminNap) {
                    $profile->edu_coin = $soDuTruocNap + $tongCoinNhan;
                    $profile->save();
                }
            });

            $user->load('thongTinNguoiDung');

            return ApiResponse::success(
                $this->toPublicArray($user),
                "Đã nạp {$tongCoinNhan} Edu Coin thành công.",
            );
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:users,id'],
            ]);

            $count = User::query()
                ->where('role', UserRole::User)
                ->whereIn('id', $validated['ids'])
                ->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} người dùng.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:users,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiUser::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiUser
                ? $validated['trang_thai']
                : TrangThaiUser::from($validated['trang_thai']);

            $count = User::query()
                ->where('role', UserRole::User)
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} người dùng.",
            );
        });
    }

    private function resolveLoaiNapTien(?User $actor): LoaiNapTien
    {
        if ($actor?->isAdmin()) {
            return LoaiNapTien::AdminNap;
        }

        return LoaiNapTien::NguoiDungNap;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildThongTinThanhToan(
        KenhThanhToan $kenhThanhToan,
        ?NganHangThanhToan $bank,
        User $nguoiNap,
    ): ?array {
        if ($kenhThanhToan !== KenhThanhToan::ChuyenKhoan || $bank === null) {
            return null;
        }

        $hoTen = trim((string) $nguoiNap->name);
        $noiDung = 'NAP EDU '.$nguoiNap->id.($hoTen !== '' ? ' '.$hoTen : '');

        return [
            'ngan_hang_thanh_toan_id' => $bank->id,
            'ten_ngan_hang' => $bank->ten_ngan_hang,
            'ten_viet_tat' => $bank->ten_viet_tat,
            'so_tai_khoan' => $bank->so_tai_khoan,
            'chu_tai_khoan' => $bank->chu_tai_khoan,
            'chi_nhanh' => $bank->chi_nhanh,
            'noi_dung_chuyen_khoan' => mb_substr($noiDung, 0, 100),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(User $user): array
    {
        /** @var NguoiDung|null $profile */
        $profile = $user->relationLoaded('thongTinNguoiDung')
            ? $user->thongTinNguoiDung
            : $user->thongTinNguoiDung()->first();

        $trangThai = $user->trang_thai instanceof TrangThaiUser
            ? $user->trang_thai
            : TrangThaiUser::tryFrom((string) $user->trang_thai);

        return [
            'id' => $user->id,
            'ho_ten' => $user->name,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai,
            'trang_thai' => $trangThai?->value ?? TrangThaiUser::DangHoatDong->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiUser::DangHoatDong->label(),
            'ngay_sinh' => $profile?->ngay_sinh,
            'gioi_tinh' => $profile?->gioi_tinh,
            'dan_toc' => $profile?->dan_toc,
            'ton_giao' => $profile?->ton_giao,
            'trinh_do_hoc_van' => $profile?->trinh_do_hoc_van,
            'suc_khoe_the_chat' => $profile?->suc_khoe_the_chat,
            'kha_nang_tai_chinh' => $profile?->kha_nang_tai_chinh,
            'vi_tri_dia_ly' => $profile?->vi_tri_dia_ly,
            'edu_coin' => (int) ($profile?->edu_coin ?? 0),
            'xu_he_thong' => (int) ($profile?->xu_he_thong ?? 0),
            'da_cai_mat_khau_thanh_toan' => filled($profile?->mat_khau_thanh_toan),
            'has_profile' => $profile !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
