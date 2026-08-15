<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Enums\TrangThaiLichSuPhien;
use App\Enums\TrangThaiThanhToanTracNghiem;
use App\Models\TracNghiemLichSuThanhToan;
use App\Models\TracNghiemPhienDaHoanThanh;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TracNghiemPhienDaHoanThanhController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));

            $query = TracNghiemPhienDaHoanThanh::query()
                ->with([
                    'nguoiKhaoSat:id,name,email',
                    'thanhToans' => fn ($q) => $q->orderByDesc('id'),
                ])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ssid', 'like', "%{$keyword}%")
                            ->orWhereHas('nguoiKhaoSat', function ($userQuery) use ($keyword) {
                                $userQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%");
                            });
                    });
                })
                ->orderByDesc('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success(
                $page['data']->map(fn (TracNghiemPhienDaHoanThanh $item) => $this->toPublicArray($item))->values(),
                'Lấy lịch sử trắc nghiệm thành công.',
                [
                    'total' => $page['total'],
                    'start' => $page['start'],
                    'limit' => $page['limit'],
                ],
            );
        });
    }

    public function show(TracNghiemPhienDaHoanThanh $phienDaHoanThanh): JsonResponse
    {
        return $this->tryApi(function () use ($phienDaHoanThanh) {
            $phienDaHoanThanh->load([
                'nguoiKhaoSat:id,name,email',
                'thanhToans' => fn ($q) => $q->orderByDesc('id'),
            ]);

            return ApiResponse::success(
                $this->toPublicArray($phienDaHoanThanh),
                'Lấy chi tiết phiên trắc nghiệm thành công.',
            );
        });
    }

    public function destroy(TracNghiemPhienDaHoanThanh $phienDaHoanThanh): JsonResponse
    {
        return $this->tryApi(function () use ($phienDaHoanThanh) {
            $phienDaHoanThanh->delete();

            return ApiResponse::success(null, 'Đã xóa phiên trắc nghiệm.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => [
                    'integer',
                    'distinct',
                    'exists:trac_nghiem_lich_su_phien,id',
                ],
            ]);

            $count = TracNghiemPhienDaHoanThanh::query()
                ->whereIn('id', $validated['ids'])
                ->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} phiên trắc nghiệm.",
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function toPublicArray(TracNghiemPhienDaHoanThanh $item): array
    {
        $nhomNganh = is_array($item->nhom_nganh) ? $item->nhom_nganh : [];
        $topNhom = $nhomNganh[0] ?? null;

        $chiTiet = is_array($item->chi_tiet_ket_qua) ? $item->chi_tiet_ket_qua : [];

        return [
            'id' => $item->id,
            'ssid' => $item->ssid,
            'trang_thai' => $item->trang_thai?->value ?? $item->trang_thai,
            'trang_thai_label' => $item->trang_thai instanceof TrangThaiLichSuPhien
                ? $item->trang_thai->label()
                : null,
            'nguoi_khao_sat_id' => $item->nguoi_khao_sat_id,
            'nguoi_khao_sat' => $item->nguoiKhaoSat
                ? [
                    'id' => $item->nguoiKhaoSat->id,
                    'name' => $item->nguoiKhaoSat->name,
                    'email' => $item->nguoiKhaoSat->email,
                ]
                : null,
            'nhom_nganh' => $nhomNganh,
            'top_nhom_nganh' => $topNhom,
            'so_nhom_nganh' => count($nhomNganh),
            'tong_diem' => $chiTiet['tong_diem'] ?? ($topNhom['tong_diem'] ?? null),
            'so_cau_da_tra_loi' => $chiTiet['so_cau_da_tra_loi'] ?? null,
            'thong_tin_thanh_toan' => $item->thong_tin_thanh_toan,
            'da_thanh_toan' => $this->isDaThanhToan($item),
            'chi_tiet_ket_qua' => $chiTiet,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }

    private function isDaThanhToan(TracNghiemPhienDaHoanThanh $item): bool
    {
        if (! empty($item->thong_tin_thanh_toan)) {
            return true;
        }

        if (! $item->relationLoaded('thanhToans')) {
            return false;
        }

        return $item->thanhToans->contains(
            fn (TracNghiemLichSuThanhToan $row) => $row->trang_thai === TrangThaiThanhToanTracNghiem::DaHoanThanh
                || $row->trang_thai === TrangThaiThanhToanTracNghiem::DaHoanThanh->value,
        );
    }
}
