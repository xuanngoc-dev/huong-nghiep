<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  Role values, e.g. admin,user
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowed = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->all();

        $userRole = $user->role instanceof UserRole
            ? $user->role->value
            : (string) $user->role;

        if (! in_array($userRole, $allowed, true)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập.',
            ], 403);
        }

        return $next($request);
    }
}
