<?php

use App\Http\Controllers\Api\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Api\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Api\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Api\Admin\ChuyenNganhController as AdminChuyenNganhController;
use App\Http\Controllers\Api\Admin\DanTocController as AdminDanTocController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\HeDaoTaoController as AdminHeDaoTaoController;
use App\Http\Controllers\Api\Admin\KhuVucController as AdminKhuVucController;
use App\Http\Controllers\Api\Admin\LoaiCauHoiController as AdminLoaiCauHoiController;
use App\Http\Controllers\Api\Admin\LoaiTruongController as AdminLoaiTruongController;
use App\Http\Controllers\Api\Admin\MonHocController as AdminMonHocController;
use App\Http\Controllers\Api\Admin\NapEduCoinController as AdminNapEduCoinController;
use App\Http\Controllers\Api\Admin\NganHangThanhToanController as AdminNganHangThanhToanController;
use App\Http\Controllers\Api\Admin\NganhHocController as AdminNganhHocController;
use App\Http\Controllers\Api\Admin\NguoiDungController as AdminNguoiDungController;
use App\Http\Controllers\Api\Admin\NhomNganhController as AdminNhomNganhController;
use App\Http\Controllers\Api\Admin\PhuongThucXetTuyenController as AdminPhuongThucXetTuyenController;
use App\Http\Controllers\Api\Admin\PhuongXaController as AdminPhuongXaController;
use App\Http\Controllers\Api\Admin\TinhThanhController as AdminTinhThanhController;
use App\Http\Controllers\Api\Admin\ToHopMonHocController as AdminToHopMonHocController;
use App\Http\Controllers\Api\Admin\TonGiaoController as AdminTonGiaoController;
use App\Http\Controllers\Api\Admin\TracNghiemCauHoiController as AdminTracNghiemCauHoiController;
use App\Http\Controllers\Api\Admin\TracNghiemCauTraLoiController as AdminTracNghiemCauTraLoiController;
use App\Http\Controllers\Api\Admin\TracNghiemPhienDaHoanThanhController as AdminTracNghiemPhienDaHoanThanhController;
use App\Http\Controllers\Api\Admin\TruongHocController as AdminTruongHocController;
use App\Http\Controllers\Api\Admin\TruongHocTuyenSinhTheoNamController as AdminTruongHocTuyenSinhTheoNamController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\LoaiCauHoiController;
use App\Http\Controllers\Api\NganhHocController;
use App\Http\Controllers\Api\NganHangThanhToanController;
use App\Http\Controllers\Api\NapEduCoinController;
use App\Http\Controllers\Api\NguoiDungController;
use App\Http\Controllers\Api\TracNghiemCauHoiController;
use App\Http\Controllers\Api\TracNghiemLichSuTraLoiController;
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
            Route::put('/doi-mat-khau', [AuthController::class, 'changePassword']);
        });
    });

    // Public site
    Route::get('/careers', [CareerController::class, 'index']);
    Route::get('/careers/{id}', [CareerController::class, 'show']);

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);

    Route::get('/assessments', [AssessmentController::class, 'index']);
    Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
    Route::get('/loai-cau-hoi', [LoaiCauHoiController::class, 'index']);
    Route::get('/nganh-hoc', [NganhHocController::class, 'index']);
    Route::get('/nganh-hoc/{nganhHoc}/truong-tuyen-sinh', [NganhHocController::class, 'truongTuyenSinh']);
    Route::get('/trac-nghiem-cau-hoi', [TracNghiemCauHoiController::class, 'index']);
    Route::post('/trac-nghiem-lich-su-tra-loi/start', [TracNghiemLichSuTraLoiController::class, 'start']);
    Route::post('/trac-nghiem-lich-su-tra-loi', [TracNghiemLichSuTraLoiController::class, 'store']);
    Route::get('/trac-nghiem-lich-su-tra-loi/{ssid}/tong-hop', [TracNghiemLichSuTraLoiController::class, 'tongHop']);
    Route::get('/trac-nghiem-lich-su-tra-loi/{ssid}', [TracNghiemLichSuTraLoiController::class, 'show']);

    Route::post('/nguoi-dung', [NguoiDungController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/nguoi-dung/me', [NguoiDungController::class, 'me']);
        Route::post('/assessments/{id}/submit', [AssessmentController::class, 'submit']);
        Route::get('/ngan-hang-thanh-toan', [NganHangThanhToanController::class, 'index']);
        Route::post('/nap-edu-coin', [NapEduCoinController::class, 'store']);
        Route::get('/nap-edu-coin/{napEduCoin}', [NapEduCoinController::class, 'show']);
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

            Route::post('phuong-xa/bulk-delete', [AdminPhuongXaController::class, 'bulkDestroy']);
            Route::post('phuong-xa/bulk-status', [AdminPhuongXaController::class, 'bulkUpdateStatus']);
            Route::apiResource('phuong-xa', AdminPhuongXaController::class)
                ->parameters(['phuong-xa' => 'phuongXa']);

            Route::post('loai-truong/bulk-delete', [AdminLoaiTruongController::class, 'bulkDestroy']);
            Route::apiResource('loai-truong', AdminLoaiTruongController::class)
                ->parameters(['loai-truong' => 'loaiTruong']);

            Route::post('he-dao-tao/bulk-delete', [AdminHeDaoTaoController::class, 'bulkDestroy']);
            Route::apiResource('he-dao-tao', AdminHeDaoTaoController::class)
                ->parameters(['he-dao-tao' => 'heDaoTao']);

            Route::post('nhom-nganh/bulk-delete', [AdminNhomNganhController::class, 'bulkDestroy']);
            Route::post('nhom-nganh/bulk-status', [AdminNhomNganhController::class, 'bulkUpdateStatus']);
            Route::apiResource('nhom-nganh', AdminNhomNganhController::class)
                ->parameters(['nhom-nganh' => 'nhomNganh']);

            Route::post('truong-hoc/bulk-delete', [AdminTruongHocController::class, 'bulkDestroy']);
            Route::post('truong-hoc/bulk-status', [AdminTruongHocController::class, 'bulkUpdateStatus']);
            Route::apiResource('truong-hoc', AdminTruongHocController::class)
                ->parameters(['truong-hoc' => 'truongHoc']);

            Route::post('truong-hoc-tuyen-sinh-theo-nam/bulk-delete', [AdminTruongHocTuyenSinhTheoNamController::class, 'bulkDestroy']);
            Route::apiResource('truong-hoc-tuyen-sinh-theo-nam', AdminTruongHocTuyenSinhTheoNamController::class)
                ->parameters(['truong-hoc-tuyen-sinh-theo-nam' => 'truongHocTuyenSinhTheoNam']);

            Route::post('loai-cau-hoi/bulk-delete', [AdminLoaiCauHoiController::class, 'bulkDestroy']);
            Route::post('loai-cau-hoi/bulk-status', [AdminLoaiCauHoiController::class, 'bulkUpdateStatus']);
            Route::apiResource('loai-cau-hoi', AdminLoaiCauHoiController::class)
                ->parameters(['loai-cau-hoi' => 'loaiCauHoi']);

            Route::post('mon-hoc/bulk-delete', [AdminMonHocController::class, 'bulkDestroy']);
            Route::apiResource('mon-hoc', AdminMonHocController::class)
                ->parameters(['mon-hoc' => 'monHoc']);

            Route::post('phuong-thuc-xet-tuyen/bulk-delete', [AdminPhuongThucXetTuyenController::class, 'bulkDestroy']);
            Route::apiResource('phuong-thuc-xet-tuyen', AdminPhuongThucXetTuyenController::class)
                ->parameters(['phuong-thuc-xet-tuyen' => 'phuongThucXetTuyen']);

            Route::post('to-hop-mon-hoc/bulk-delete', [AdminToHopMonHocController::class, 'bulkDestroy']);
            Route::apiResource('to-hop-mon-hoc', AdminToHopMonHocController::class)
                ->parameters(['to-hop-mon-hoc' => 'toHopMonHoc']);

            Route::post('dan-toc/bulk-delete', [AdminDanTocController::class, 'bulkDestroy']);
            Route::post('dan-toc/bulk-status', [AdminDanTocController::class, 'bulkUpdateStatus']);
            Route::apiResource('dan-toc', AdminDanTocController::class)
                ->parameters(['dan-toc' => 'danToc']);

            Route::post('ton-giao/bulk-delete', [AdminTonGiaoController::class, 'bulkDestroy']);
            Route::post('ton-giao/bulk-status', [AdminTonGiaoController::class, 'bulkUpdateStatus']);
            Route::apiResource('ton-giao', AdminTonGiaoController::class)
                ->parameters(['ton-giao' => 'tonGiao']);

            Route::post('ngan-hang-thanh-toan/bulk-delete', [AdminNganHangThanhToanController::class, 'bulkDestroy']);
            Route::post('ngan-hang-thanh-toan/bulk-status', [AdminNganHangThanhToanController::class, 'bulkUpdateStatus']);
            Route::apiResource('ngan-hang-thanh-toan', AdminNganHangThanhToanController::class)
                ->parameters(['ngan-hang-thanh-toan' => 'nganHangThanhToan']);

            Route::post('nguoi-dung/bulk-delete', [AdminNguoiDungController::class, 'bulkDestroy']);
            Route::post('nguoi-dung/bulk-status', [AdminNguoiDungController::class, 'bulkUpdateStatus']);
            Route::put('nguoi-dung/{user}/doi-mat-khau', [AdminNguoiDungController::class, 'changePassword']);
            Route::post('nguoi-dung/{user}/nap-tien', [AdminNguoiDungController::class, 'napTien']);
            Route::apiResource('nguoi-dung', AdminNguoiDungController::class)
                ->only(['index', 'show', 'update', 'destroy'])
                ->parameters(['nguoi-dung' => 'user']);

            Route::post('lich-su-trac-nghiem/bulk-delete', [AdminTracNghiemPhienDaHoanThanhController::class, 'bulkDestroy']);
            Route::apiResource('lich-su-trac-nghiem', AdminTracNghiemPhienDaHoanThanhController::class)
                ->only(['index', 'show', 'destroy'])
                ->parameters(['lich-su-trac-nghiem' => 'phienDaHoanThanh']);

            Route::get('lich-su-nap-coin', [AdminNapEduCoinController::class, 'index']);
            Route::get('lich-su-nap-coin/{napEduCoin}', [AdminNapEduCoinController::class, 'show']);
            Route::post('lich-su-nap-coin/{napEduCoin}/duyet', [AdminNapEduCoinController::class, 'duyet']);
            Route::post('lich-su-nap-coin/{napEduCoin}/huy-duyet', [AdminNapEduCoinController::class, 'huyDuyet']);

            Route::post('trac-nghiem-cau-hoi/bulk-delete', [AdminTracNghiemCauHoiController::class, 'bulkDestroy']);
            Route::post('trac-nghiem-cau-hoi/bulk-status', [AdminTracNghiemCauHoiController::class, 'bulkUpdateStatus']);
            Route::apiResource('trac-nghiem-cau-hoi', AdminTracNghiemCauHoiController::class)
                ->parameters(['trac-nghiem-cau-hoi' => 'tracNghiemCauHoi']);

            Route::post('trac-nghiem-cau-tra-loi/bulk-delete', [AdminTracNghiemCauTraLoiController::class, 'bulkDestroy']);
            Route::apiResource('trac-nghiem-cau-tra-loi', AdminTracNghiemCauTraLoiController::class)
                ->parameters(['trac-nghiem-cau-tra-loi' => 'tracNghiemCauTraLoi']);
        });
});
