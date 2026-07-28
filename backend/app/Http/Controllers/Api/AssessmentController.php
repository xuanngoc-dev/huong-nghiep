<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    private array $assessments = [
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
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->assessments,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $assessment = collect($this->assessments)->firstWhere('id', $id);

        if (! $assessment) {
            return response()->json(['message' => 'Không tìm thấy bài trắc nghiệm.'], 404);
        }

        return response()->json([
            'data' => array_merge($assessment, [
                'questions' => [],
            ]),
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $assessment = collect($this->assessments)->firstWhere('id', $id);

        if (! $assessment) {
            return response()->json(['message' => 'Không tìm thấy bài trắc nghiệm.'], 404);
        }

        $request->validate([
            'answers' => ['required', 'array'],
        ]);

        return response()->json([
            'message' => 'Nộp bài thành công (placeholder).',
            'data' => [
                'assessment_id' => $id,
                'user_id' => $request->user()->id,
                'result' => [
                    'summary' => 'Kết quả sẽ được tính toán khi hoàn thiện logic.',
                ],
            ],
        ]);
    }
}
