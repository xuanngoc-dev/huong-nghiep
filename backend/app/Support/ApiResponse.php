<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Phản hồi thành công — luôn kèm status = true.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function success(
        mixed $data = null,
        string $message = 'Thành công.',
        array $extra = [],
        int $httpStatus = 200,
    ): JsonResponse {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            ...$extra,
        ], $httpStatus);
    }

    /**
     * Phản hồi lỗi — luôn kèm status = false (HTTP 200 để FE đọc body qua status).
     *
     * @param  array<string, mixed>  $extra
     */
    public static function error(
        string $message = 'Đã xảy ra lỗi.',
        mixed $data = null,
        array $extra = [],
        int $httpStatus = 200,
    ): JsonResponse {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            ...$extra,
        ], $httpStatus);
    }
}
