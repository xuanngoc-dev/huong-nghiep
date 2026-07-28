<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TracNghiemCauTraLoi;
use App\Support\ApiResponse;
use App\Support\OffsetPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TracNghiemCauTraLoiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $cauHoiId = $request->query('cau_hoi_id');

            $query = TracNghiemCauTraLoi::query()
                ->when(
                    filled($cauHoiId) && is_numeric($cauHoiId),
                    fn ($query) => $query->where('cau_hoi_id', (int) $cauHoiId),
                )
                ->orderBy('id');

            $page = OffsetPaginator::paginate($query, $request);

            return ApiResponse::success($page['data'], 'Lấy danh sách câu trả lời thành công.', [
                'total' => $page['total'],
                'start' => $page['start'],
                'limit' => $page['limit'],
            ]);
        });
    }

    public function show(TracNghiemCauTraLoi $tracNghiemCauTraLoi): JsonResponse
    {
        return $this->tryApi(function () use ($tracNghiemCauTraLoi) {
            return ApiResponse::success($tracNghiemCauTraLoi, 'Lấy chi tiết câu trả lời thành công.');
        });
    }

    public function store(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate(
                [
                    'cau_hoi_id' => ['required', 'integer', 'exists:trac_nghiem_cau_hoi,id'],
                    'noi_dung_cau_tra_loi' => ['required', 'string'],
                    'diem' => ['required', 'numeric', 'between:0,10'],
                ],
                [
                    'diem.between' => 'Điểm phải nằm trong khoảng từ 0 đến 10.',
                ],
            );

            $item = TracNghiemCauTraLoi::create($validated);

            return ApiResponse::success($item, 'Tạo câu trả lời thành công.');
        });
    }

    public function update(Request $request, TracNghiemCauTraLoi $tracNghiemCauTraLoi): JsonResponse
    {
        return $this->tryApi(function () use ($request, $tracNghiemCauTraLoi) {
            $validated = $request->validate(
                [
                    'cau_hoi_id' => ['sometimes', 'integer', 'exists:trac_nghiem_cau_hoi,id'],
                    'noi_dung_cau_tra_loi' => ['sometimes', 'string'],
                    'diem' => ['sometimes', 'numeric', 'between:0,10'],
                ],
                [
                    'diem.between' => 'Điểm phải nằm trong khoảng từ 0 đến 10.',
                ],
            );

            $tracNghiemCauTraLoi->update($validated);

            return ApiResponse::success($tracNghiemCauTraLoi->fresh(), 'Cập nhật câu trả lời thành công.');
        });
    }

    public function destroy(TracNghiemCauTraLoi $tracNghiemCauTraLoi): JsonResponse
    {
        return $this->tryApi(function () use ($tracNghiemCauTraLoi) {
            $tracNghiemCauTraLoi->delete();

            return ApiResponse::success(null, 'Đã xóa câu trả lời.');
        });
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->tryApi(function () use ($request) {
            $validated = $request->validate([
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'distinct', 'exists:trac_nghiem_cau_tra_loi,id'],
            ]);

            $count = TracNghiemCauTraLoi::query()->whereIn('id', $validated['ids'])->delete();

            return ApiResponse::success(
                ['deleted' => $count],
                "Đã xóa {$count} câu trả lời.",
            );
        });
    }
}
