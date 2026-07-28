<?php

use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Api\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Api\Admin\ChuyenNganhController as AdminChuyenNganhController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\HeDaoTaoController as AdminHeDaoTaoController;
use App\Http\Controllers\Api\Admin\KhuVucController as AdminKhuVucController;
use App\Http\Controllers\Api\Admin\LoaiTruongController as AdminLoaiTruongController;
use App\Http\Controllers\Api\Admin\NganhHocController as AdminNganhHocController;
use App\Http\Controllers\Api\Admin\TinhThanhController as AdminTinhThanhController;
use App\Http\Controllers\Api\Admin\TruongHocController as AdminTruongHocController;
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

            Route::post('chuyen-nganh/bulk-delete', [AdminChuyenNganhController::class, 'bulkDestroy']);
            Route::post('chuyen-nganh/bulk-status', [AdminChuyenNganhController::class, 'bulkUpdateStatus']);
            Route::apiResource('chuyen-nganh', AdminChuyenNganhController::class)
                ->parameters(['chuyen-nganh' => 'chuyenNganh']);

            Route::post('khu-vuc/bulk-delete', [AdminKhuVucController::class, 'bulkDestroy']);
            Route::post('khu-vuc/bulk-status', [AdminKhuVucController::class, 'bulkUpdateStatus']);
            Route::apiResource('khu-vuc', AdminKhuVucController::class)
                ->parameters(['khu-vuc' => 'khuVuc']);

            Route::post('tinh-thanh/bulk-delete', [AdminTinhThanhController::class, 'bulkDestroy']);
            Route::post('tinh-thanh/bulk-status', [AdminTinhThanhController::class, 'bulkUpdateStatus']);
            Route::apiResource('tinh-thanh', AdminTinhThanhController::class)
                ->parameters(['tinh-thanh' => 'tinhThanh']);

            Route::post('loai-truong/bulk-delete', [AdminLoaiTruongController::class, 'bulkDestroy']);
            Route::apiResource('loai-truong', AdminLoaiTruongController::class)
                ->parameters(['loai-truong' => 'loaiTruong']);

            Route::post('he-dao-tao/bulk-delete', [AdminHeDaoTaoController::class, 'bulkDestroy']);
            Route::apiResource('he-dao-tao', AdminHeDaoTaoController::class)
                ->parameters(['he-dao-tao' => 'heDaoTao']);

            Route::post('truong-hoc/bulk-delete', [AdminTruongHocController::class, 'bulkDestroy']);
            Route::post('truong-hoc/bulk-status', [AdminTruongHocController::class, 'bulkUpdateStatus']);
            Route::apiResource('truong-hoc', AdminTruongHocController::class)
                ->parameters(['truong-hoc' => 'truongHoc']);
        });
});
