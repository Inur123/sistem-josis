<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AkunController as AdminAkunController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PemilihController as AdminPemilihController;
use App\Http\Controllers\Admin\RelawanController;
use App\Http\Controllers\Admin\TimController as AdminTimController;
use App\Http\Controllers\Admin\TpsController as AdminTpsController;
use App\Http\Controllers\Desa\DashboardController as DesaDashboardController;
use App\Http\Controllers\Desa\DataSuaraController as DesaDataSuaraController;
use App\Http\Controllers\Desa\PemilihController as DesaPemilihController;
use App\Http\Controllers\Desa\TpsController as DesaTpsController;
use App\Http\Controllers\Kecamatan\DashboardController as KecamatanDashboardController;
use App\Http\Controllers\Kecamatan\PemilihController as KecamatanPemilihController;
use App\Http\Controllers\Kecamatan\TpsController as KecamatanTpsController;
use App\Http\Controllers\PemilihKtpController;
use App\Http\Controllers\TpsHasilController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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
    /** @var User $user */
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
        Route::get('/pemilih/export', [AdminPemilihController::class, 'export'])->name('pemilih.export');
        Route::get('/pemilih/{pemilih}', [AdminPemilihController::class, 'show'])->name('pemilih.show');
        Route::post('/pemilih/{pemilih}/verify', [AdminPemilihController::class, 'verify'])->name('pemilih.verify');
        Route::get('/akun', [AdminAkunController::class, 'index'])->name('akun.index');
        Route::get('/akun/export', [AdminAkunController::class, 'export'])->name('akun.export');
        Route::put('/akun/{user}', [AdminAkunController::class, 'update'])->name('akun.update');
        Route::delete('/akun/{user}', [AdminAkunController::class, 'destroy'])->name('akun.destroy');
        Route::get('/activity-logs', AdminActivityLogController::class)->name('activity-logs');
        Route::get('/relawan', [RelawanController::class, 'index'])->name('relawan.index');
        Route::get('/relawan/{relawan}', [RelawanController::class, 'show'])->name('relawan.show');
        Route::get('/relawan/{relawan}/pemilih/{pemilih}', [RelawanController::class, 'showPemilih'])->name('relawan.pemilih.show');
        Route::get('/relawan/{relawan}/pemilihs', [RelawanController::class, 'pemilihs'])->name('relawan.pemilihs');
        Route::get('/tim/export', [AdminTimController::class, 'export'])->name('tim.export');
        Route::resource('/tim', AdminTimController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/tps', [AdminTpsController::class, 'index'])->name('tps.index');
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
        Route::get('/pemilih/{pemilih}', [KecamatanPemilihController::class, 'show'])->name('pemilih.show');
        Route::get('/relawan', [App\Http\Controllers\Kecamatan\RelawanController::class, 'index'])->name('relawan.index');
        Route::get('/relawan/{relawan}', [App\Http\Controllers\Kecamatan\RelawanController::class, 'show'])->name('relawan.show');
        Route::get('/relawan/{relawan}/pemilih/{pemilih}', [App\Http\Controllers\Kecamatan\RelawanController::class, 'showPemilih'])->name('relawan.pemilih.show');
        Route::get('/relawan/{relawan}/pemilihs', [App\Http\Controllers\Kecamatan\RelawanController::class, 'pemilihs'])->name('relawan.pemilihs');
        Route::get('/tps', [KecamatanTpsController::class, 'index'])->name('tps.index');
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
        Route::get('/pemilih', [DesaPemilihController::class, 'index'])->name('pemilih.index');
        Route::get('/pemilih/tambah', [DesaPemilihController::class, 'create'])->name('pemilih.create');
        Route::post('/pemilih', [DesaPemilihController::class, 'store'])->name('pemilih.store');
        Route::get('/pemilih/{pemilih}', [DesaPemilihController::class, 'show'])->name('pemilih.show');
        Route::get('/pemilih/{pemilih}/edit', [DesaPemilihController::class, 'edit'])->name('pemilih.edit');
        Route::put('/pemilih/{pemilih}', [DesaPemilihController::class, 'update'])->name('pemilih.update');
        Route::delete('/pemilih/{pemilih}', [DesaPemilihController::class, 'destroy'])->name('pemilih.destroy');
        Route::get('/relawan', [App\Http\Controllers\Desa\RelawanController::class, 'index'])->name('relawan.index');
        Route::get('/relawan/{relawan}', [App\Http\Controllers\Desa\RelawanController::class, 'show'])->name('relawan.show');
        Route::get('/relawan/{relawan}/pemilih/{pemilih}', [App\Http\Controllers\Desa\RelawanController::class, 'showPemilih'])->name('relawan.pemilih.show');
        Route::get('/relawan/{relawan}/pemilihs', [App\Http\Controllers\Desa\RelawanController::class, 'pemilihs'])->name('relawan.pemilihs');
        Route::resource('/tps', DesaTpsController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/data-suara', [DesaDataSuaraController::class, 'index'])->name('data-suara.index');
        Route::post('/data-suara', [DesaDataSuaraController::class, 'store'])->name('data-suara.store');
        Route::post('/data-suara/{tps}/upload', [DesaDataSuaraController::class, 'uploadCHasil'])->name('data-suara.upload');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/pemilih/{pemilih}/ktp', [PemilihKtpController::class, 'show'])->name('pemilih.ktp');
    Route::get('/tps/{tps}/c-hasil', [TpsHasilController::class, 'show'])->name('tps.c-hasil');
});
