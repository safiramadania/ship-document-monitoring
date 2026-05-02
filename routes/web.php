<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountStatusController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/approval-pending', [AccountStatusController::class, 'pending'])
        ->name('approval.pending');
    Route::get('/account-rejected', [AccountStatusController::class, 'rejected'])
        ->name('approval.rejected');
});

Route::middleware(['auth', 'verified', 'active', 'last.seen'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/dashboard-cabang', fn () => Inertia::render('DashboardCabang'))
        ->middleware('role:user_cabang')
        ->name('dashboard.cabang');
    Route::get('/monitoring-kapal', fn () => Inertia::render('MonitoringKapal'))->name('monitoring.index');
    Route::get('/upload-dokumen', fn () => Inertia::render('UploadDokumen'))->name('uploads.index');
    Route::get('/smart-upload', fn () => Inertia::render('SmartUpload'))->name('uploads.smart');
    Route::get('/ocr-confirmation', fn () => Inertia::render('OcrConfirmation'))->name('ocr.confirmation');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/user-approval', [UserApprovalController::class, 'index'])->name('users.approval');
        Route::patch('/user-approval/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.approve');
        Route::patch('/user-approval/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.reject');
        Route::get('/branches', fn () => Inertia::render('Branches'))->name('branches.index');
        Route::get('/vessels', fn () => Inertia::render('Vessels'))->name('vessels.index');
        Route::get('/document-types', fn () => Inertia::render('DocumentTypes'))->name('document-types.index');
        Route::get('/master-data', fn () => Inertia::render('MasterData'))->name('master-data.index');
        Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings.index');
    });

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/email-logs', fn () => Inertia::render('EmailLogs'))->name('email-logs.index');
        Route::get('/audit-logs', fn () => Inertia::render('AuditLogs'))->name('audit-logs.index');
    });

    Route::middleware('role:user_cabang')->group(function () {
        Route::get('/dokumen-saya', fn () => Inertia::render('DokumenSaya'))->name('documents.mine');
    });
});

require __DIR__.'/auth.php';
