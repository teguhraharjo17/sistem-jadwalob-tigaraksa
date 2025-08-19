<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Checklist\ChecklistController;
use \App\Http\Controllers\LaporanHarian\LaporanHarianController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Routes yang dimuat oleh RouteServiceProvider dan diberikan grup "web"
| middleware. Silakan atur sesuai kebutuhan aplikasi.
|--------------------------------------------------------------------------
*/

// ===========================
// Public Routes (Tanpa Auth)
// ===========================
Route::get('/error', fn() => abort(500));

require __DIR__.'/auth.php';


// ===========================
// Protected Routes (dengan Auth)
// ===========================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Checklist Area Pembersihan
    Route::prefix('checklist')->name('checklist.')->group(function () {
        Route::get('/', [ChecklistController::class, 'index'])->name('index');
        Route::post('/', [ChecklistController::class, 'store'])->name('store');
    });

    Route::prefix('laporanharian')->name('laporanharian.')->group(function () {
        Route::get('/', [LaporanHarianController::class, 'index'])->name('index');
        Route::post('/', [LaporanHarianController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [LaporanHarianController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LaporanHarianController::class, 'update'])->name('update');
    });


    // Admin-only routes
    Route::prefix('admin')->name('admin.')->middleware('can:isAdmin')->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('make-account');
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    });
});
