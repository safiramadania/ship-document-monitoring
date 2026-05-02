<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountStatusController;
use App\Http\Controllers\AuditLogsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\EmailLogsController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OcrConfirmationController;
use App\Http\Controllers\SmartUploadController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TargetedUploadController;
use Illuminate\Http\Request;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-cabang', [DashboardController::class, 'cabang'])
        ->middleware('role:user_cabang')
        ->name('dashboard.cabang');
    Route::get('/monitoring-kapal', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/upload-dokumen', fn () => Inertia::render('UploadDokumen'))->name('uploads.index');
    Route::get('/targeted-upload/{vessel}/{documentType}', [TargetedUploadController::class, 'create'])
        ->name('targeted-uploads.create');
    Route::post('/targeted-upload/{vessel}/{documentType}', [TargetedUploadController::class, 'store'])
        ->name('targeted-uploads.store');
    Route::get('/documents/{vesselDocument}/preview', [DocumentFileController::class, 'preview'])
        ->name('documents.preview');
    Route::get('/documents/{vesselDocument}/download', [DocumentFileController::class, 'download'])
        ->name('documents.download');
    Route::get('/smart-upload', [SmartUploadController::class, 'index'])->name('uploads.smart');
    Route::post('/smart-upload', [SmartUploadController::class, 'store'])->name('uploads.smart.store');
    Route::get('/ocr-confirmation', function (Request $request) {
        $documentId = $request->integer('vessel_document_id');

        abort_unless($documentId, 404);

        return redirect()->route('ocr.confirmation', $documentId);
    })->name('ocr.confirmation.legacy');
    Route::get('/ocr-confirmation/{vesselDocument}', [OcrConfirmationController::class, 'show'])
        ->name('ocr.confirmation');
    Route::put('/ocr-confirmation/{vesselDocument}', [OcrConfirmationController::class, 'confirm'])
        ->name('ocr.confirmation.confirm');

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
        Route::get('/email-logs', [EmailLogsController::class, 'index'])->name('email-logs.index');
        Route::post('/email-logs/send-reminders', [EmailLogsController::class, 'sendReminders'])->name('email-logs.send-reminders');
        Route::get('/audit-logs', [AuditLogsController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('role:user_cabang')->group(function () {
        Route::get('/dokumen-saya', fn () => Inertia::render('DokumenSaya'))->name('documents.mine');
    });
});

require __DIR__.'/auth.php';
