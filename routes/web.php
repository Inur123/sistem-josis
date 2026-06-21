<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PemilihController as AdminPemilihController;
use App\Http\Controllers\Admin\AkunController as AdminAkunController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Kecamatan\DashboardController as KecamatanDashboardController;
use App\Http\Controllers\Kecamatan\PemilihController as KecamatanPemilihController;
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

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    // Reflash session agar flash message (seperti toast sukses login) bertahan melewati double redirect
    session()->reflash();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'kecamatan') {
        return redirect()->route('kecamatan.dashboard');
    }

    if ($user->role === 'desa') {
        return redirect()->route('desa.dashboard');
    }

    abort(403, 'Role tidak valid.');
})->middleware(['auth'])->name('dashboard');

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
        Route::put('/akun/{user}', [AdminAkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{user}', [AdminAkunController::class, 'destroy'])->name('akun.destroy');
        Route::get('/activity-logs', AdminActivityLogController::class)->name('activity-logs');
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
        Route::get('/pemilih', [KecamatanPemilihController::class, 'index'])->name('pemilih.index');
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
