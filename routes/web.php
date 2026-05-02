<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/dashboard-cabang', fn () => Inertia::render('DashboardCabang'))->name('dashboard.cabang');
    Route::get('/monitoring-kapal', fn () => Inertia::render('MonitoringKapal'))->name('monitoring.index');
    Route::get('/upload-dokumen', fn () => Inertia::render('UploadDokumen'))->name('uploads.index');
    Route::get('/smart-upload', fn () => Inertia::render('SmartUpload'))->name('uploads.smart');
    Route::get('/ocr-confirmation', fn () => Inertia::render('OcrConfirmation'))->name('ocr.confirmation');
    Route::get('/user-approval', fn () => Inertia::render('UserApproval'))->name('users.approval');
    Route::get('/users', fn () => Inertia::render('Users'))->name('users.index');
    Route::get('/branches', fn () => Inertia::render('Branches'))->name('branches.index');
    Route::get('/vessels', fn () => Inertia::render('Vessels'))->name('vessels.index');
    Route::get('/document-types', fn () => Inertia::render('DocumentTypes'))->name('document-types.index');
    Route::get('/master-data', fn () => Inertia::render('MasterData'))->name('master-data.index');
    Route::get('/email-logs', fn () => Inertia::render('EmailLogs'))->name('email-logs.index');
    Route::get('/audit-logs', fn () => Inertia::render('AuditLogs'))->name('audit-logs.index');
    Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings.index');
    Route::get('/dokumen-saya', fn () => Inertia::render('DokumenSaya'))->name('documents.mine');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
