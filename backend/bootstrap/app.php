<?php

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Không bật statefulApi(): FE dùng Bearer token (Sanctum), không dùng cookie/CSRF SPA.
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'anti.spam' => \App\Http\Middleware\AntiSpamApi::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\AntiSpamApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                $message = collect($e->errors())->flatten()->first()
                    ?? 'Dữ liệu không hợp lệ.';

                return ApiResponse::error($message, null, [
                    'errors' => $e->errors(),
                ]);
            }

            if ($e instanceof AuthenticationException) {
                return ApiResponse::error('Unauthenticated.', null, [], 401);
            }

            if ($e instanceof ModelNotFoundException) {
                return ApiResponse::error('Không tìm thấy dữ liệu.');
            }

            if ($e instanceof HttpExceptionInterface) {
                $statusCode = $e->getStatusCode();
                $httpStatus = in_array($statusCode, [401, 403, 429], true) ? $statusCode : 200;
                $message = $e->getMessage() !== ''
                    ? $e->getMessage()
                    : 'Đã xảy ra lỗi.';

                $extra = [];
                if ($statusCode === 429) {
                    $extra['code'] = 'ANTI_SPAM';
                    $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 0);
                    if ($retryAfter > 0) {
                        $extra['retry_after'] = $retryAfter;
                    }
                }

                return ApiResponse::error($message, null, $extra, $httpStatus);
            }

            report($e);

            $message = config('app.debug')
                ? $e->getMessage()
                : 'Đã xảy ra lỗi hệ thống.';

            return ApiResponse::error($message);
        });
    })->create();
