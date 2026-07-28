<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

trait HandlesApiResponse
{
    /**
     * Bọc logic API trong try/catch — luôn trả JSON có status.
     */
    protected function tryApi(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'Dữ liệu không hợp lệ.';

            return ApiResponse::error($message, null, [
                'errors' => $e->errors(),
            ]);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Không tìm thấy dữ liệu.');
        } catch (Throwable $e) {
            report($e);

            $message = config('app.debug')
                ? $e->getMessage()
                : 'Đã xảy ra lỗi hệ thống.';

            return ApiResponse::error($message);
        }
    }
}
