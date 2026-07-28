<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CareerController extends Controller
{
    /**
     * Placeholder data — sẽ thay bằng Eloquent khi có migration/model.
     */
    private array $careers = [
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
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->careers,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $career = collect($this->careers)->firstWhere('id', $id);

        if (! $career) {
            return response()->json(['message' => 'Không tìm thấy nghề nghiệp.'], 404);
        }

        return response()->json(['data' => $career]);
    }
}
