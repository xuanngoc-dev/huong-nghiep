<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    private array $articles = [
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
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->articles,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $article = collect($this->articles)->firstWhere('id', $id);

        if (! $article) {
            return response()->json(['message' => 'Không tìm thấy bài viết.'], 404);
        }

        return response()->json([
            'data' => array_merge($article, [
                'content' => 'Nội dung chi tiết sẽ được bổ sung sau.',
            ]),
        ]);
    }
}
