<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardBIController;
use App\Http\Controllers\Exports\KinerjaExport;
use App\Http\Controllers\ComparisonController;


/*
|--------------------------------------------------------------------------
| Web Routes (PUBLIK / TANPA LOGIN)
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardBIController::class, 'index'])->name('home');
Route::get('/api/bi-data', [DashboardBIController::class, 'getBiData'])->name('api.bi.data');
Route::get('/quick-compare', [ComparisonController::class, 'index'])->name('compare.index');
Route::get('/quick-compare', [ComparisonController::class, 'index'])->name('compare.index');
Route::get('/api/admr-reference', [ComparisonController::class, 'getAdmrData']);
/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'authenticate']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Admin Routes (PERLU LOGIN)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // CRUD & Input Data
        Route::get('/input', [AdminController::class, 'input'])->name('input');
        Route::post('/store', [AdminController::class, 'store'])->name('store');
        Route::post('/input/parse-excel', [AdminController::class, 'parseExcel'])->name('input.parse');

        // Riwayat & Detail (Disederhanakan agar pas dengan Dashboard)
        Route::get('/riwayat', [AdminController::class, 'riwayat'])->name('riwayat');
        Route::get('/detail/{id}', [AdminController::class, 'show'])->name('show'); // Nama rute jadi 'admin.show'
        Route::delete('/destroy/{id}', [AdminController::class, 'destroy'])->name('destroy');

        // Module Reporting
        Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
        Route::get('/laporan/pdf', [AdminController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('/laporan/excel', [AdminController::class, 'exportExcel'])->name('laporan.excel');
    });
