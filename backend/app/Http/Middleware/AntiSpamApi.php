<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AntiSpamApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('anti_spam.enabled', true)) {
            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $key = $this->clientKey($request);
        $blockKey = "anti_spam:block:{$key}";
        $hitsKey = "anti_spam:hits:{$key}";
        $warnKey = "anti_spam:warns:{$key}";

        // Đang bị khóa
        if (Cache::has($blockKey)) {
            $retryAfter = $this->retryAfterSeconds($blockKey);

            return $this->blockedResponse($retryAfter);
        }

        $maxAttempts = max(1, (int) config('anti_spam.max_attempts', 30));
        $window = max(1, (int) config('anti_spam.window_seconds', 60));

        $hits = (int) Cache::get($hitsKey, 0) + 1;
        Cache::put($hitsKey, $hits, $window);

        if ($hits <= $maxAttempts) {
            return $next($request);
        }

        // Vượt ngưỡng → cảnh báo hoặc khóa
        $maxWarnings = max(1, (int) config('anti_spam.max_warnings', 2));
        $warnDecay = max(1, (int) config('anti_spam.warning_decay_seconds', 300));
        $blockSeconds = max(1, (int) config('anti_spam.block_seconds', 60));

        $warnings = (int) Cache::get($warnKey, 0) + 1;
        Cache::put($warnKey, $warnings, $warnDecay);

        // Reset hits để chu kỳ spam tiếp theo còn được đếm lại
        Cache::forget($hitsKey);

        if ($warnings >= $maxWarnings) {
            Cache::put($blockKey, true, $blockSeconds);
            Cache::put("{$blockKey}:until", now()->addSeconds($blockSeconds)->timestamp, $blockSeconds);
            Cache::forget($warnKey);

            return $this->blockedResponse($blockSeconds);
        }

        return ApiResponse::error('Vui lòng thao tác đúng.', null, [
            'code' => 'ANTI_SPAM_WARN',
            'warnings' => $warnings,
            'max_warnings' => $maxWarnings,
        ], 429)
            ->header('Retry-After', (string) $window);
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = trim($request->path(), '/'); // api/v1/...
        $path = preg_replace('#^api/#', '', $path) ?? $path;

        foreach (config('anti_spam.except', []) as $except) {
            $except = trim((string) $except, '/');
            if ($except !== '' && ($path === $except || str_starts_with($path, $except.'/'))) {
                return true;
            }
        }

        return false;
    }

    protected function clientKey(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier() ?? 'guest';
        $ip = $request->ip() ?: 'unknown';
        $route = $request->route()?->getName()
            ?: ($request->method().':'.$request->path());

        return hash('xxh128', "{$userId}|{$ip}|{$route}");
    }

    protected function retryAfterSeconds(string $blockKey): int
    {
        $until = (int) Cache::get("{$blockKey}:until", 0);
        if ($until > 0) {
            return max(1, $until - time());
        }

        return max(1, (int) config('anti_spam.block_seconds', 60));
    }

    protected function blockedResponse(int $retryAfter): Response
    {
        $seconds = max(1, $retryAfter);
        $message = $seconds >= 60
            ? sprintf('Bạn đã thao tác quá nhiều lần. Vui lòng thử lại sau %d phút.', (int) ceil($seconds / 60))
            : sprintf('Bạn đã thao tác quá nhiều lần. Vui lòng thử lại sau %d giây.', $seconds);

        return ApiResponse::error($message, null, [
            'code' => 'ANTI_SPAM_BLOCKED',
            'retry_after' => $seconds,
        ], 429)->header('Retry-After', (string) $seconds);
    }
}
