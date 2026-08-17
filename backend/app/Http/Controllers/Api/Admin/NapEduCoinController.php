<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\KenhThanhToan;
use App\Enums\TrangThaiYeuCauNapEduCoin;
use App\Http\Controllers\Controller;
use App\Models\NapEduCoin;
use App\Models\User;
use App\Services\DuyetYeuCauNapEduCoin;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NapEduCoinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = NapEduCoin::query()
                ->with([
                    'nguoiNap:id,name,email,so_dien_thoai',
                    'nguoiDuyet:id,name,email',
                ])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ghi_chu', 'like', "%{$keyword}%")
                            ->orWhere('ma_giao_dich', 'like', "%{$keyword}%")
                            ->orWhereHas('nguoiNap', function ($userQuery) use ($keyword) {
                                $userQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('so_dien_thoai', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiYeuCauNapEduCoin::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (NapEduCoin $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử nạp Edu Coin thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(NapEduCoin $napEduCoin): JsonResponse
    {
        return $this->tryApi(function () use ($napEduCoin) {
            $napEduCoin->load([
                'nguoiNap:id,name,email,so_dien_thoai',
                'nguoiDuyet:id,name,email',
            ]);

            return ApiResponse::success(
                $this->toPublicArray($napEduCoin),
                'Lấy chi tiết yêu cầu nạp Edu Coin thành công.',
            );
        });
    }

    public function duyet(Request $request, NapEduCoin $napEduCoin): JsonResponse
    {
        return $this->tryApi(function () use ($request, $napEduCoin) {
            $actor = $request->user();

            $updated = DB::transaction(function () use ($napEduCoin, $actor) {
                /** @var NapEduCoin $item */
                $item = NapEduCoin::query()->lockForUpdate()->findOrFail($napEduCoin->id);

                return (new DuyetYeuCauNapEduCoin)->duyet($item, $actor?->id);
            });

            if ($updated === null) {
                return ApiResponse::error('Yêu cầu nạp không còn ở trạng thái chờ duyệt.');
            }

            $updated->load([
                'nguoiNap:id,name,email,so_dien_thoai',
                'nguoiDuyet:id,name,email',
            ]);

            return ApiResponse::success(
                $this->toPublicArray($updated),
                "Đã duyệt và cộng {$updated->so_luong_edu_coin} Edu Coin.",
            );
        });
    }

    public function huyDuyet(Request $request, NapEduCoin $napEduCoin): JsonResponse
    {
        return $this->tryApi(function () use ($request, $napEduCoin) {
            $actor = $request->user();

            $updated = DB::transaction(function () use ($napEduCoin, $actor) {
                /** @var NapEduCoin $item */
                $item = NapEduCoin::query()->lockForUpdate()->findOrFail($napEduCoin->id);
                if ($item->trang_thai !== TrangThaiYeuCauNapEduCoin::ChoDuyet) {
                    return null;
                }

                $item->nguoi_duyet_id = $actor?->id;
                $item->trang_thai = TrangThaiYeuCauNapEduCoin::HuyDuyet;
                $item->save();

                return $item;
            });

            if ($updated === null) {
                return ApiResponse::error('Yêu cầu nạp không còn ở trạng thái chờ duyệt.');
            }

            $updated->load([
                'nguoiNap:id,name,email,so_dien_thoai',
                'nguoiDuyet:id,name,email',
            ]);

            return ApiResponse::success(
                $this->toPublicArray($updated),
                'Đã từ chối duyệt yêu cầu nạp Edu Coin.',
            );
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toUserArray(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai ?? null,
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

        $thongTin = is_array($item->thong_tin_thanh_toan) ? $item->thong_tin_thanh_toan : [];

        return [
            'id' => $item->id,
            'ma_giao_dich' => $item->ma_giao_dich,
            'nguoi_nap_id' => $item->nguoi_nap_id,
            'nguoi_nap' => $this->toUserArray($item->nguoiNap),
            'nguoi_duyet_id' => $item->nguoi_duyet_id,
            'nguoi_duyet' => $this->toUserArray($item->nguoiDuyet),
            'so_luong_edu_coin' => (int) $item->so_luong_edu_coin,
            'so_tien_nap' => (int) $item->so_tien_nap,
            'kenh_thanh_toan' => $kenh?->value,
            'kenh_thanh_toan_label' => $kenh?->label(),
            'thong_tin_thanh_toan' => $thongTin,
            'ten_ngan_hang' => $thongTin['ten_ngan_hang'] ?? null,
            'trang_thai' => $trangThai?->value ?? TrangThaiYeuCauNapEduCoin::ChoDuyet->value,
            'trang_thai_label' => $trangThai?->label() ?? TrangThaiYeuCauNapEduCoin::ChoDuyet->label(),
            'ghi_chu' => $item->ghi_chu,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }
}
