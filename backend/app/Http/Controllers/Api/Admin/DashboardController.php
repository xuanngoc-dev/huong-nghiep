<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'users_count' => User::query()->count(),
                'admins_count' => User::query()->where('role', 'admin')->count(),
                'careers_count' => 3,
                'articles_count' => 2,
                'assessments_count' => 2,
            ],
        ]);
    }
}
