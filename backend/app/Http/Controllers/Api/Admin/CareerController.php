<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Kỹ sư phần mềm',
                    'slug' => 'ky-su-phan-mem',
                    'description' => 'Thiết kế, phát triển và bảo trì hệ thống phần mềm.',
                    'category' => 'Công nghệ thông tin',
                ],
                [
                    'id' => 2,
                    'name' => 'Nhà thiết kế UX/UI',
                    'slug' => 'nha-thiet-ke-ux-ui',
                    'description' => 'Thiết kế trải nghiệm và giao diện người dùng.',
                    'category' => 'Thiết kế',
                ],
                [
                    'id' => 3,
                    'name' => 'Chuyên viên marketing',
                    'slug' => 'chuyen-vien-marketing',
                    'description' => 'Lập kế hoạch và triển khai chiến lược marketing.',
                    'category' => 'Kinh doanh',
                ],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $id,
                'name' => 'Nghề mẫu',
                'slug' => 'nghe-mau',
                'description' => 'Chi tiết nghề (placeholder CMS).',
                'category' => 'Khác',
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'message' => 'Tạo nghề nghiệp thành công (placeholder — chưa có DB).',
            'data' => array_merge(['id' => 0], $validated),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'message' => 'Cập nhật nghề nghiệp thành công (placeholder).',
            'data' => array_merge(['id' => $id], $validated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'message' => "Đã xóa nghề nghiệp #{$id} (placeholder).",
        ]);
    }
}
