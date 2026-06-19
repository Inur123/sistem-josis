<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PemilihController as AdminPemilihController;
use App\Http\Controllers\Admin\AkunController as AdminAkunController;
use App\Http\Controllers\Kecamatan\DashboardController as KecamatanDashboardController;
use App\Http\Controllers\Desa\DashboardController as DesaDashboardController;
use App\Http\Controllers\Desa\PemilihController as DesaPemilihController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'))->name('home');

/*
|--------------------------------------------------------------------------
| Redirect setelah login — sesuai role
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/dashboard', function (\Illuminate\Http\Request $request) {
    $role = $request->user()->role;

    return match ($role) {
        'admin'     => redirect()->route('admin.dashboard'),
        'kecamatan' => redirect()->route('kecamatan.dashboard'),
        'desa'      => redirect()->route('desa.dashboard'),
        default     => abort(403),
    };
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin routes — hanya role: admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/pemilih', [AdminPemilihController::class, 'index'])->name('pemilih.index');
        Route::get('/akun', [AdminAkunController::class, 'index'])->name('akun.index');
        Route::post('/akun', [AdminAkunController::class, 'store'])->name('akun.store');
        Route::put('/akun/{user}', [AdminAkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{user}', [AdminAkunController::class, 'destroy'])->name('akun.destroy');
    });

/*
|--------------------------------------------------------------------------
| Kecamatan routes — hanya role: kecamatan
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:kecamatan'])
    ->prefix('kecamatan')
    ->name('kecamatan.')
    ->group(function () {
        Route::get('/dashboard', KecamatanDashboardController::class)->name('dashboard');
        Route::get('/pemilih', fn () => Inertia::render('kecamatan/Pemilih'))->name('pemilih.index');
    });

/*
|--------------------------------------------------------------------------
| Desa routes — hanya role: desa
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:desa'])
    ->prefix('desa')
    ->name('desa.')
    ->group(function () {
        Route::get('/dashboard', DesaDashboardController::class)->name('dashboard');
        Route::get('/pemilih',                 [DesaPemilihController::class, 'index'])  ->name('pemilih.index');
        Route::get('/pemilih/tambah',          [DesaPemilihController::class, 'create']) ->name('pemilih.create');
        Route::post('/pemilih',                [DesaPemilihController::class, 'store'])  ->name('pemilih.store');
        Route::get('/pemilih/{pemilih}/edit',  [DesaPemilihController::class, 'edit'])   ->name('pemilih.edit');
        Route::put('/pemilih/{pemilih}',       [DesaPemilihController::class, 'update']) ->name('pemilih.update');
        Route::delete('/pemilih/{pemilih}',    [DesaPemilihController::class, 'destroy'])->name('pemilih.destroy');
    });
