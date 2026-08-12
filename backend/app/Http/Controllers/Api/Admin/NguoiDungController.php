<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NguoiDungController extends Controller
{
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
            'ngay_sinh' => $profile?->ngay_sinh?->format('Y-m-d'),
            'gioi_tinh' => $profile?->gioi_tinh,
            'dan_toc' => $profile?->dan_toc,
            'ton_giao' => $profile?->ton_giao,
            'trinh_do_hoc_van' => $profile?->trinh_do_hoc_van,
            'suc_khoe_the_chat' => $profile?->suc_khoe_the_chat,
            'kha_nang_tai_chinh' => $profile?->kha_nang_tai_chinh,
            'vi_tri_dia_ly' => $profile?->vi_tri_dia_ly,
            'edu_coin' => (int) ($profile?->edu_coin ?? 0),
            'xu_he_thong' => (int) ($profile?->xu_he_thong ?? 0),
            'has_profile' => $profile !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
