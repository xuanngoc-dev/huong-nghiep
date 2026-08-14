<?php

namespace App\Http\Controllers\Api;

use App\Enums\HinhThucNhanXu;
use App\Enums\TrangThaiNhanXu;
use App\Http\Controllers\Controller;
use App\Models\LichSuNhanXu;
use App\Models\NguoiDung;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiemDanhXuHeThongController extends Controller
{
    public function tuan(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để xem điểm danh.');
            }

            return ApiResponse::success(
                $this->buildTuanPayload($user->id),
                'Lấy lịch điểm danh tuần này thành công.',
            );
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $user = $request->user();
            if (! $user) {
                return ApiResponse::error('Bạn cần đăng nhập để điểm danh.');
            }

            $today = $this->nowVn()->toDateString();
            $soXu = LichSuNhanXu::SO_XU_DIEM_DANH;

            try {
                $result = DB::transaction(function () use ($user, $today, $soXu) {
                    $profile = NguoiDung::query()
                        ->where('user_id', $user->id)
                        ->lockForUpdate()
                        ->first();

                    if ($profile === null) {
                        $profile = NguoiDung::query()->create([
                            'user_id' => $user->id,
                            'edu_coin' => 0,
                            'xu_he_thong' => 0,
                        ]);
                    }

                    $daDiemDanh = LichSuNhanXu::query()
                        ->where('nguoi_dung_id', $user->id)
                        ->where('hinh_thuc_nhan_xu', HinhThucNhanXu::DiemDanh)
                        ->whereDate('ngay_nhan', $today)
                        ->lockForUpdate()
                        ->exists();

                    if ($daDiemDanh) {
                        return null;
                    }

                    $soDuTruoc = (int) $profile->xu_he_thong;
                    $soDuSau = $soDuTruoc + $soXu;

                    $profile->xu_he_thong = $soDuSau;
                    $profile->save();

                    $lichSu = LichSuNhanXu::query()->create([
                        'nguoi_dung_id' => $user->id,
                        'hinh_thuc_nhan_xu' => HinhThucNhanXu::DiemDanh,
                        'ngay_nhan' => $today,
                        'so_du_truoc_khi_nhan' => $soDuTruoc,
                        'so_xu_nhan_duoc' => $soXu,
                        'so_du_sau_khi_nhan' => $soDuSau,
                        'trang_thai' => TrangThaiNhanXu::ThanhCong,
                    ]);

                    return [
                        'xu_he_thong' => $soDuSau,
                        'lich_su' => $lichSu,
                    ];
                });
            } catch (UniqueConstraintViolationException) {
                return ApiResponse::error('Bạn đã điểm danh hôm nay.');
            }

            if ($result === null) {
                return ApiResponse::error('Bạn đã điểm danh hôm nay.');
            }

            return ApiResponse::success(
                [
                    'xu_he_thong' => $result['xu_he_thong'],
                    'so_xu_nhan_duoc' => $soXu,
                    'tuan' => $this->buildTuanPayload($user->id, $result['xu_he_thong']),
                ],
                'Điểm danh thành công. Bạn nhận được '.number_format($soXu, 0, ',', '.').' xu hệ thống.',
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTuanPayload(int $userId, ?int $xuHeThong = null): array
    {
        $now = $this->nowVn();
        $today = $now->toDateString();
        $start = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = $now->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();

        if ($xuHeThong === null) {
            $xuHeThong = (int) (NguoiDung::query()
                ->where('user_id', $userId)
                ->value('xu_he_thong') ?? 0);
        }

        $claimedDates = LichSuNhanXu::query()
            ->where('nguoi_dung_id', $userId)
            ->where('hinh_thuc_nhan_xu', HinhThucNhanXu::DiemDanh)
            ->whereBetween('ngay_nhan', [$start->toDateString(), $end->toDateString()])
            ->pluck('ngay_nhan')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();

        $claimedLookup = array_fill_keys($claimedDates, true);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $ngay = $date->toDateString();
            $daDiemDanh = isset($claimedLookup[$ngay]);
            $isToday = $ngay === $today;
            $isFuture = $date->gt($now->copy()->startOfDay());
            $isPast = $date->lt($now->copy()->startOfDay());

            $days[] = [
                'ngay' => $ngay,
                'thu' => $date->dayOfWeekIso,
                'thu_label' => $this->thuLabel($date->dayOfWeekIso),
                'ngay_label' => $date->format('d/m'),
                'da_diem_danh' => $daDiemDanh,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'is_future' => $isFuture,
                'co_the_diem_danh' => $isToday && ! $daDiemDanh,
            ];
        }

        return [
            'xu_he_thong' => $xuHeThong,
            'so_xu_moi_ngay' => LichSuNhanXu::SO_XU_DIEM_DANH,
            'hom_nay' => $today,
            'tuan_bat_dau' => $start->toDateString(),
            'tuan_ket_thuc' => $end->toDateString(),
            'days' => $days,
        ];
    }

    private function nowVn(): Carbon
    {
        return Carbon::now(LichSuNhanXu::TIMEZONE);
    }

    private function thuLabel(int $isoDay): string
    {
        return match ($isoDay) {
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            default => 'CN',
        };
    }
}
