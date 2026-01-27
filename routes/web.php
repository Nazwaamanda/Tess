<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
// Import Controller BI (Pastikan namespace ini benar sesuai file controllernya)
use App\Http\Controllers\DashboardBIController;

/*
|--------------------------------------------------------------------------
| Web Routes (PUBLIK / TANPA LOGIN)
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardBIController::class, 'index'])->name('home');
Route::get('/api/bi-data', [DashboardBIController::class, 'getBiData'])->name('api.bi.data');
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

        // Dashboard Admin (Beda tampilan dengan welcome, ini untuk admin panel)
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // CRUD & Input Data
        Route::get('/input', [AdminController::class, 'input'])->name('input');
        Route::post('/input/store', [AdminController::class, 'store'])->name('input.store');
        Route::post('/input/parse-excel', [AdminController::class, 'parseExcel'])->name('input.parse');

        // Riwayat & Detail
        Route::get('/riwayat', [AdminController::class, 'riwayat'])->name('riwayat');
        Route::get('/riwayat/{id}', [AdminController::class, 'show'])->name('riwayat.show');
        Route::delete('/riwayat/{id}', [AdminController::class, 'destroy'])->name('riwayat.destroy');
    });
