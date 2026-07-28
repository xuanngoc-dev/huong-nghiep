<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Trắc nghiệm Holland (RIASEC)',
                    'slug' => 'holland-riasec',
                    'description' => 'Khám phá nhóm tính cách nghề nghiệp theo mô hình Holland.',
                    'question_count' => 60,
                ],
                [
                    'id' => 2,
                    'name' => 'Đánh giá năng lực bản thân',
                    'slug' => 'danh-gia-nang-luc',
                    'description' => 'Tự đánh giá kỹ năng và sở thích học tập.',
                    'question_count' => 30,
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'message' => 'Tạo trắc nghiệm thành công (placeholder).',
            'data' => array_merge(['id' => 0, 'question_count' => 0], $validated),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'message' => 'Cập nhật trắc nghiệm thành công (placeholder).',
            'data' => array_merge(['id' => $id], $validated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'message' => "Đã xóa trắc nghiệm #{$id} (placeholder).",
        ]);
    }
}
