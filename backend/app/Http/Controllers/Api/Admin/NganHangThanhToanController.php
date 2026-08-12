<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiNganHangThanhToan;
use App\Http\Controllers\Controller;
use App\Models\NganHangThanhToan;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NganHangThanhToanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = NganHangThanhToan::query()
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_ngan_hang', 'like', "%{$keyword}%")
                            ->orWhere('ten_viet_tat', 'like', "%{$keyword}%")
                            ->orWhere('so_tai_khoan', 'like', "%{$keyword}%")
                            ->orWhere('chu_tai_khoan', 'like', "%{$keyword}%")
                            ->orWhere('chi_nhanh', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiNganHangThanhToan::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('ten_ngan_hang');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách ngân hàng thanh toán thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(NganHangThanhToan $nganHangThanhToan): JsonResponse
    {
        return $this->tryApi(function () use ($nganHangThanhToan) {
            return ApiResponse::success($nganHangThanhToan, 'Lấy chi tiết ngân hàng thanh toán thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_ngan_hang' => ['required', 'string', 'max:255'],
                'ten_viet_tat' => ['nullable', 'string', 'max:50'],
                'hinh_anh_logo' => ['nullable', 'string', 'max:255'],
                'so_tai_khoan' => ['required', 'string', 'max:50'],
                'chu_tai_khoan' => ['required', 'string', 'max:255'],
                'chi_nhanh' => ['nullable', 'string', 'max:255'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganHangThanhToan::class)],
                'ghi_chu' => ['nullable', 'string', 'max:2000'],
            ]);

            $item = NganHangThanhToan::create($validated);

            return ApiResponse::success($item, 'Tạo ngân hàng thanh toán thành công.');
        });
    }

    public function update(Request $request, NganHangThanhToan $nganHangThanhToan): JsonResponse
    {
        return $this->tryApi(function () use ($request, $nganHangThanhToan) {
            $validated = $request->validate([
                'ten_ngan_hang' => ['sometimes', 'string', 'max:255'],
                'ten_viet_tat' => ['nullable', 'string', 'max:50'],
                'hinh_anh_logo' => ['nullable', 'string', 'max:255'],
                'so_tai_khoan' => ['sometimes', 'string', 'max:50'],
                'chu_tai_khoan' => ['sometimes', 'string', 'max:255'],
                'chi_nhanh' => ['nullable', 'string', 'max:255'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiNganHangThanhToan::class)],
                'ghi_chu' => ['nullable', 'string', 'max:2000'],
            ]);

            $nganHangThanhToan->update($validated);

            return ApiResponse::success($nganHangThanhToan->fresh(), 'Cập nhật ngân hàng thanh toán thành công.');
        });
    }

    public function destroy(NganHangThanhToan $nganHangThanhToan): JsonResponse
    {
        return $this->tryApi(function () use ($nganHangThanhToan) {
            $nganHangThanhToan->delete();

            return ApiResponse::success(null, 'Đã xóa ngân hàng thanh toán.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:he_thong_ngan_hang_thanh_toan,id'],
            ]);

            $count = NganHangThanhToan::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} ngân hàng thanh toán.",
            );
        });
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:he_thong_ngan_hang_thanh_toan,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiNganHangThanhToan::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiNganHangThanhToan
                ? $validated['trang_thai']
                : TrangThaiNganHangThanhToan::from($validated['trang_thai']);

            $count = NganHangThanhToan::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} ngân hàng thanh toán.",
            );
        });
    }
}
