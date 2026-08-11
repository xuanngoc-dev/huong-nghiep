<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\TrangThaiLoaiCauHoi;
use App\Http\Controllers\Controller;
use App\Models\LoaiCauHoi;
use App\Models\TracNghiemCauHoi;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoaiCauHoiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $keyword = trim((string) $request->query('q', ''));
            $trangThai = trim((string) $request->query('trang_thai', ''));

            $query = LoaiCauHoi::query()
                ->withCount(['cauHois as so_luong_cau_hoi'])
                ->when($keyword !== '', function ($query) use ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('ten_loai_cau_hoi', 'like', "%{$keyword}%")
                            ->orWhere('ma_loai_cau_hoi', 'like', "%{$keyword}%");
                    });
                })
                ->when(
                    $trangThai !== '' && TrangThaiLoaiCauHoi::tryFrom($trangThai) !== null,
                    fn ($query) => $query->where('trang_thai', $trangThai),
                )
                ->orderBy('thu_tu_uu_tien')
                ->orderBy('ten_loai_cau_hoi');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách loại câu hỏi thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(LoaiCauHoi $loaiCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($loaiCauHoi) {
            $loaiCauHoi->loadCount(['cauHois as so_luong_cau_hoi']);

            return ApiResponse::success($loaiCauHoi, 'Lấy chi tiết loại câu hỏi thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ten_loai_cau_hoi' => ['required', 'string', 'max:255'],
                'ma_loai_cau_hoi' => ['required', 'string', 'max:50', 'unique:danh_muc_loai_cau_hoi,ma_loai_cau_hoi'],
                'ghi_chu' => ['nullable', 'string'],
                'thu_tu_uu_tien' => ['required', 'integer', 'min:1'],
                'trang_thai' => ['required', Rule::enum(TrangThaiLoaiCauHoi::class)],
            ]);

            $item = LoaiCauHoi::create($validated);
            $item->loadCount(['cauHois as so_luong_cau_hoi']);

            return ApiResponse::success($item, 'Tạo loại câu hỏi thành công.');
        });
    }

    public function update(Request $request, LoaiCauHoi $loaiCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($request, $loaiCauHoi) {
            $validated = $request->validate([
                'ten_loai_cau_hoi' => ['sometimes', 'string', 'max:255'],
                'ma_loai_cau_hoi' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('danh_muc_loai_cau_hoi', 'ma_loai_cau_hoi')->ignore($loaiCauHoi->id),
                ],
                'ghi_chu' => ['nullable', 'string'],
                'thu_tu_uu_tien' => ['sometimes', 'integer', 'min:1'],
                'trang_thai' => ['sometimes', Rule::enum(TrangThaiLoaiCauHoi::class)],
            ]);

            $loaiCauHoi->update($validated);
            $loaiCauHoi = $loaiCauHoi->fresh();
            $loaiCauHoi->loadCount(['cauHois as so_luong_cau_hoi']);

            return ApiResponse::success($loaiCauHoi, 'Cập nhật loại câu hỏi thành công.');
        });
    }

    public function destroy(LoaiCauHoi $loaiCauHoi): JsonResponse
    {
        return $this->tryApi(function () use ($loaiCauHoi) {
            $this->assertLoaiCauHoiDeletable([(int) $loaiCauHoi->id]);

            $loaiCauHoi->delete();

            return ApiResponse::success(null, 'Đã xóa loại câu hỏi.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_loai_cau_hoi,id'],
            ]);

            $ids = array_values(array_map('intval', $validated['ids']));
            $this->assertLoaiCauHoiDeletable($ids);

            $count = LoaiCauHoi::query()->whereIn('id', $ids)->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} loại câu hỏi.",
            );
        });
    }

    /**
     * Chặn xóa khi loại câu hỏi vẫn còn câu hỏi liên kết.
     *
     * @param  list<int>  $loaiCauHoiIds
     */
    private function assertLoaiCauHoiDeletable(array $loaiCauHoiIds): void
    {
        $loaiCauHoiIds = array_values(array_unique(array_filter(
            array_map('intval', $loaiCauHoiIds),
            fn (int $id) => $id > 0,
        )));

        if ($loaiCauHoiIds === []) {
            return;
        }

        $linkedIds = TracNghiemCauHoi::query()
            ->whereIn('loai_cau_hoi_id', $loaiCauHoiIds)
            ->distinct()
            ->pluck('loai_cau_hoi_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($linkedIds === []) {
            return;
        }

        $blocked = LoaiCauHoi::query()
            ->whereIn('id', $linkedIds)
            ->orderBy('ten_loai_cau_hoi')
            ->get(['id', 'ten_loai_cau_hoi']);

        if ($blocked->count() === 1) {
            $ten = $blocked->first()->ten_loai_cau_hoi;

            throw ValidationException::withMessages([
                'ids' => ["Không thể xóa loại câu hỏi «{$ten}» vì vẫn còn câu hỏi thuộc loại này."],
            ]);
        }

        $tenList = $blocked->pluck('ten_loai_cau_hoi')->implode(', ');

        throw ValidationException::withMessages([
            'ids' => ["Không thể xóa các loại câu hỏi sau vì vẫn còn câu hỏi liên kết: {$tenList}."],
        ]);
    }

    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:danh_muc_loai_cau_hoi,id'],
                'trang_thai' => ['required', Rule::enum(TrangThaiLoaiCauHoi::class)],
            ]);

            $trangThai = $validated['trang_thai'] instanceof TrangThaiLoaiCauHoi
                ? $validated['trang_thai']
                : TrangThaiLoaiCauHoi::from($validated['trang_thai']);

            $count = LoaiCauHoi::query()
                ->whereIn('id', $validated['ids'])
                ->update(['trang_thai' => $trangThai->value]);

            return ApiResponse::success(
                ['updated' => $count],
                "Đã cập nhật trạng thái «{$trangThai->label()}» cho {$count} loại câu hỏi.",
            );
        });
    }
}
