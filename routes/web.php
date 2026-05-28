<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboard;
use App\Http\Controllers\Supervisor\SupplierController;
use App\Http\Controllers\Supervisor\ProdukController;
use App\Http\Controllers\Supervisor\KriteriaController;
use App\Http\Controllers\Supervisor\SubkriteriaController;
use App\Http\Controllers\Supervisor\AhpController;
use App\Http\Controllers\Supervisor\LaporanController as SupervisorLaporan;

use App\Http\Controllers\Sales\DashboardController as SalesDashboard;
use App\Http\Controllers\Sales\PengadaanController;
use App\Http\Controllers\Sales\LaporanController as SalesLaporan;

use App\Http\Controllers\Logistik\DashboardController as LogistikDashboard;
use App\Http\Controllers\Logistik\AktualPengadaanController;
use App\Http\Controllers\Logistik\LaporanController as LogistikLaporan;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Supervisor routes
    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorDashboard::class, 'index'])->name('dashboard');
        Route::resource('supplier', SupplierController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('subkriteria', SubkriteriaController::class);

        // AHP Stepper
        Route::get('/ahp/kriteria', [AhpController::class, 'kriteriaForm'])->name('ahp.kriteria');
        Route::post('/ahp/kriteria', [AhpController::class, 'kriteriaSave']);
        
        Route::get('/ahp/subkriteria', [AhpController::class, 'subkriteriaForm'])->name('ahp.subkriteria');
        Route::post('/ahp/subkriteria', [AhpController::class, 'subkriteriaSave']);

        Route::get('/ahp/supplier', [AhpController::class, 'supplierForm'])->name('ahp.supplier');
        Route::post('/ahp/supplier', [AhpController::class, 'supplierSave']);

        Route::get('/ahp/hasil', [AhpController::class, 'hasil'])->name('ahp.hasil');

        // Laporan
        Route::get('/laporan/penilaian', [SupervisorLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [SupervisorLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/profil', [SupervisorLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [SupervisorLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });

    // Sales routes
    Route::middleware('role:sales')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/dashboard', [SalesDashboard::class, 'index'])->name('dashboard');
        Route::resource('pengadaan', PengadaanController::class);

        // Laporan Read-only
        Route::get('/laporan/penilaian', [SalesLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [SalesLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/profil', [SalesLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [SalesLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });

    // Logistik routes
    Route::middleware('role:logistik')->prefix('logistik')->name('logistik.')->group(function () {
        Route::get('/dashboard', [LogistikDashboard::class, 'index'])->name('dashboard');
        
        // Penerimaan Aktual
        Route::get('/aktual', [AktualPengadaanController::class, 'index'])->name('aktual.index');
        Route::get('/aktual/{id}/edit', [AktualPengadaanController::class, 'edit'])->name('aktual.edit');
        Route::put('/aktual/{id}', [AktualPengadaanController::class, 'update'])->name('aktual.update');

        // Laporan Read-only
        Route::get('/laporan/penilaian', [LogistikLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [LogistikLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/profil', [LogistikLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [LogistikLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });
});
