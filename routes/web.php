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
use App\Http\Controllers\Sales\PurchaseOrderController;
use App\Http\Controllers\Sales\LaporanController as SalesLaporan;

use App\Http\Controllers\Logistik\DashboardController as LogistikDashboard;
use App\Http\Controllers\Logistik\AktualPengadaanController;
use App\Http\Controllers\Logistik\PenerimaanController;
use App\Http\Controllers\Logistik\LaporanController as LogistikLaporan;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Supervisor routes
    // EnsureRole middleware: aborts 403 on role mismatch, redirects to login when unauthenticated (Req 14.5, 14.6)
    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorDashboard::class, 'index'])->name('dashboard');
        Route::resource('supplier', SupplierController::class);
        Route::resource('produk', ProdukController::class);
        Route::resource('kriteria', KriteriaController::class);
        Route::resource('subkriteria', SubkriteriaController::class);

        // AHP Stepper
        Route::get('/ahp/alternatif', [AhpController::class, 'alternatifForm'])->name('ahp.alternatif');
        Route::post('/ahp/alternatif', [AhpController::class, 'alternatifSave']);

        Route::get('/ahp/kriteria', [AhpController::class, 'kriteriaForm'])->name('ahp.kriteria');
        Route::post('/ahp/kriteria', [AhpController::class, 'kriteriaSave']);
        
        Route::get('/ahp/subkriteria', [AhpController::class, 'subkriteriaForm'])->name('ahp.subkriteria');
        Route::post('/ahp/subkriteria', [AhpController::class, 'subkriteriaSave']);

        Route::get('/ahp/supplier', [AhpController::class, 'supplierForm'])->name('ahp.supplier');
        Route::post('/ahp/supplier', [AhpController::class, 'supplierSave']);

        Route::get('/ahp/hasil', [AhpController::class, 'hasil'])->name('ahp.hasil');

        // Laporan — Supervisor only (Req 14.1, 14.4, 14.5)
        // supervisor.laporan.penilaian (AHP Hasil Penilaian) is exclusively inside this role:supervisor group.
        // Logistik and Sales cannot access this route — EnsureRole aborts 403 if they attempt direct access.
        Route::get('/laporan/kinerja', [SupervisorLaporan::class, 'kinerja'])->name('laporan.kinerja');
        Route::get('/laporan/penilaian/pdf', [SupervisorLaporan::class, 'penilaianPdf'])->name('laporan.penilaian.pdf');
        Route::get('/laporan/penilaian', [SupervisorLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [SupervisorLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/riwayat/{id}', [SupervisorLaporan::class, 'riwayatDetail'])->name('laporan.riwayat.detail');
        Route::get('/laporan/profil', [SupervisorLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [SupervisorLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });

    // Sales routes
    // EnsureRole middleware: aborts 403 on role mismatch, redirects to login when unauthenticated (Req 14.5, 14.6)
    Route::middleware('role:sales')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/dashboard', [SalesDashboard::class, 'index'])->name('dashboard');
        Route::resource('pengadaan', PengadaanController::class);
        Route::resource('purchase-order', PurchaseOrderController::class)->except(['edit', 'update', 'destroy'])->names('purchase_order');

        // Laporan Read-only
        Route::get('/laporan/penilaian', [SalesLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [SalesLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/profil', [SalesLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [SalesLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });

    // Logistik routes
    // EnsureRole middleware: aborts 403 on role mismatch, redirects to login when unauthenticated (Req 14.5, 14.6)
    // NOTE: logistik.laporan.penilaian below is handled by LogistikLaporan (a separate controller from
    // SupervisorLaporan). It is NOT the AHP Hasil Penilaian page. The AHP supervisor.laporan.penilaian
    // is exclusively in the role:supervisor group above and returns 403 for any non-supervisor direct request. (Req 14.4)
    Route::middleware('role:logistik')->prefix('logistik')->name('logistik.')->group(function () {
        Route::get('/dashboard', [LogistikDashboard::class, 'index'])->name('dashboard');
        
        // Penerimaan Aktual
        Route::get('/aktual', [AktualPengadaanController::class, 'index'])->name('aktual.index');
        Route::get('/aktual/{id}/edit', [AktualPengadaanController::class, 'edit'])->name('aktual.edit');
        Route::put('/aktual/{id}', [AktualPengadaanController::class, 'update'])->name('aktual.update');

        // Penerimaan Barang (header/detail)
        Route::get('/penerimaan', [PenerimaanController::class, 'index'])->name('penerimaan.index');
        Route::get('/penerimaan/{penerimaan}/edit', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
        Route::put('/penerimaan/{penerimaan}', [PenerimaanController::class, 'update'])->name('penerimaan.update');

        // Laporan Read-only
        Route::get('/laporan/penilaian', [LogistikLaporan::class, 'penilaian'])->name('laporan.penilaian');
        Route::get('/laporan/pengadaan', [LogistikLaporan::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/profil', [LogistikLaporan::class, 'profil'])->name('laporan.profil');
        Route::get('/laporan/profil/{id}', [LogistikLaporan::class, 'profilDetail'])->name('laporan.profil.detail');
    });
});
