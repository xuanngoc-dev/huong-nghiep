<?php

use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Api\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\NganhHocController as AdminNganhHocController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CareerController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Public site
    Route::get('/careers', [CareerController::class, 'index']);
    Route::get('/careers/{id}', [CareerController::class, 'show']);

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);

    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::get('/assessments/{id}', [AssessmentController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/assessments/{id}/submit', [AssessmentController::class, 'submit']);
    });

    // CMS / Admin — chỉ role admin
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'role:admin'])
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index']);

            Route::apiResource('careers', AdminCareerController::class);
            Route::apiResource('articles', AdminArticleController::class)->except(['show']);
            Route::apiResource('assessments', AdminAssessmentController::class)->except(['show']);
            Route::post('nganh-hoc/bulk-delete', [AdminNganhHocController::class, 'bulkDestroy']);
            Route::post('nganh-hoc/bulk-status', [AdminNganhHocController::class, 'bulkUpdateStatus']);
            Route::apiResource('nganh-hoc', AdminNganhHocController::class)
                ->parameters(['nganh-hoc' => 'nganhHoc']);
        });
});
