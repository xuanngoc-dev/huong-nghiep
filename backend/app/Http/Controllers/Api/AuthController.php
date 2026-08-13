<?php

namespace App\Http\Controllers\Api;

use App\Enums\TrangThaiUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'so_dien_thoai' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s()-]{8,30}$/'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'so_dien_thoai.regex' => 'Số điện thoại không hợp lệ.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
            'password' => $validated['password'],
            'role' => UserRole::User,
            'trang_thai' => TrangThaiUser::DangHoatDong,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công.',
            'user' => $this->toAuthUser($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tai_khoan' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'tai_khoan.required' => 'Vui lòng nhập email hoặc số điện thoại.',
        ]);

        $login = trim($validated['tai_khoan']);
        $phoneNormalized = preg_replace('/[\s()\-]/', '', $login) ?: $login;

        $user = User::query()
            ->where(function ($query) use ($login, $phoneNormalized) {
                $query->where('email', $login)
                    ->orWhere('so_dien_thoai', $login);

                if ($phoneNormalized !== $login) {
                    $query->orWhere('so_dien_thoai', $phoneNormalized);
                }
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'tai_khoan' => ['Email/số điện thoại hoặc mật khẩu không đúng.'],
            ]);
        }

        if ($user->trang_thai === TrangThaiUser::NgungHoatDong) {
            throw ValidationException::withMessages([
                'tai_khoan' => ['Tài khoản đã bị khóa. Vui lòng liên hệ CSKH để được hỗ trợ.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công.',
            'user' => $this->toAuthUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đăng xuất thành công.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->toAuthUser($request->user()),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'current_password' => ['required', 'string'],
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
                'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
                'password.required' => 'Vui lòng nhập mật khẩu mới.',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
                'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            ]);

            $user = $request->user();
            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Mật khẩu hiện tại không đúng.'],
                ]);
            }

            if (Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Mật khẩu mới phải khác mật khẩu hiện tại.'],
                ]);
            }

            $user->password = $validated['password'];
            $user->save();

            return ApiResponse::success(null, 'Đổi mật khẩu thành công.');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toAuthUser(User $user): array
    {
        $profile = $user->thongTinNguoiDung()->first(['edu_coin', 'xu_he_thong']);
        $payload = $user->toArray();
        unset($payload['thong_tin_nguoi_dung']);

        $payload['edu_coin'] = (int) ($profile?->edu_coin ?? 0);
        $payload['xu_he_thong'] = (int) ($profile?->xu_he_thong ?? 0);

        return $payload;
    }
}
