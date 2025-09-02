<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardDataController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Checklist\ChecklistController;
use App\Http\Controllers\LaporanHarian\LaporanHarianController;

require __DIR__ . '/auth.php';

// ===========================
// Public Routes (Tanpa Auth)
// ===========================
Route::get('/error', fn () => abort(500));

// ===========================
// Protected Routes
// ===========================
Route::middleware(['auth', 'verified'])->group(function () {

    // ===========================
    // Dashboard (Grouped)
    // ===========================
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/data/{year}', [DashboardDataController::class, 'charts'])->name('data');
    });

    Route::get('/', fn () => redirect()->route('dashboard.index'));

    // ===========================
    // Logout
    // ===========================
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ===========================
    // Checklist Area Pembersihan
    // ===========================
    Route::prefix('checklist')->name('checklist.')->group(function () {
        Route::get('/', [ChecklistController::class, 'index'])->name('index');
        Route::post('/', [ChecklistController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ChecklistController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ChecklistController::class, 'update'])->name('update');
    });

    // ===========================
    // Laporan Harian
    // ===========================
    Route::prefix('laporanharian')->name('laporanharian.')->group(function () {
        Route::get('/', [LaporanHarianController::class, 'index'])->name('index');
        Route::post('/', [LaporanHarianController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [LaporanHarianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LaporanHarianController::class, 'update'])->name('update');
        Route::get('/pekerjaan-tersedia', [LaporanHarianController::class, 'getPekerjaanTersedia'])->name('pekerjaan-tersedia');
        Route::get('/export-excel', [LaporanHarianController::class, 'exportExcel'])->name('exportexcel');
        Route::post('/approval', [LaporanHarianController::class, 'storeApproval'])->name('storeapproval');
    });

    // ===========================
    // Admin Only
    // ===========================
    Route::prefix('admin')->name('admin.')->middleware('can:isAdmin')->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('make-account');
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    });
});
