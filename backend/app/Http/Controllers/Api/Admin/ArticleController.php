<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'title' => 'Cách chọn ngành phù hợp với bản thân',
                    'slug' => 'cach-chon-nganh-phu-hop',
                    'excerpt' => 'Những bước cơ bản giúp học sinh định hướng nghề nghiệp.',
                    'published_at' => '2026-07-01',
                ],
                [
                    'id' => 2,
                    'title' => 'Kỹ năng mềm cần thiết cho sinh viên',
                    'slug' => 'ky-nang-mem-cho-sinh-vien',
                    'excerpt' => 'Giao tiếp, làm việc nhóm và quản lý thời gian.',
                    'published_at' => '2026-07-15',
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        return response()->json([
            'message' => 'Tạo bài viết thành công (placeholder).',
            'data' => array_merge(['id' => 0], $validated),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
        ]);

        return response()->json([
            'message' => 'Cập nhật bài viết thành công (placeholder).',
            'data' => array_merge(['id' => $id], $validated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'message' => "Đã xóa bài viết #{$id} (placeholder).",
        ]);
    }
}
