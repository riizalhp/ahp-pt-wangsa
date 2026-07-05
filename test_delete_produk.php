<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST DELETE PRODUK + AUTO UPDATE KINERJA ===\n\n";

// 1. Cek data sebelum hapus
echo "--- SEBELUM HAPUS ---\n";
$produk = \App\Models\Produk::find(2);
if ($produk) {
    echo "Produk ID 2: {$produk->nama} (Merk: {$produk->merk}, Ukuran: {$produk->ukuran})\n";
    echo "Supplier: " . $produk->supplier->nama . "\n\n";
} else {
    echo "Produk ID 2 tidak ditemukan (mungkin sudah dihapus).\n\n";
    exit;
}

$supplier = \App\Models\Supplier::find(2);
echo "Kinerja Supplier PT ABC INDO SEBELUM:\n";
echo "- Mean Hari Keterlambatan: {$supplier->mean_hari_keterlambatan}\n";
echo "- Total Persen Cacat: {$supplier->total_persen_cacat}\n";
echo "- Total Persen Keterlambatan: {$supplier->total_persen_keterlambatan}\n\n";

$pengadaan = \App\Models\Pengadaan::where('produk_id', 2)->first();
echo "Pengadaan yang menggunakan produk ini: " . ($pengadaan ? "ID {$pengadaan->id}" : "Tidak ada") . "\n\n";

// 2. Simulasi delete (via controller logic)
echo "--- MENGHAPUS PRODUK ---\n";
\DB::beginTransaction();

try {
    // Collect affected suppliers
    $affectedSupplierIds = [];
    
    $pengadaanSupplierIds = \App\Models\Pengadaan::where('produk_id', $produk->id)
        ->pluck('supplier_id')
        ->unique()
        ->toArray();
    $affectedSupplierIds = array_merge($affectedSupplierIds, $pengadaanSupplierIds);
    
    $detailSupplierIds = \App\Models\PengadaanDetail::where('produk_id', $produk->id)
        ->with('header:id,supplier_id')
        ->get()
        ->pluck('header.supplier_id')
        ->unique()
        ->filter()
        ->toArray();
    $affectedSupplierIds = array_merge($affectedSupplierIds, $detailSupplierIds);
    
    if ($produk->supplier_id) {
        $affectedSupplierIds[] = $produk->supplier_id;
    }
    
    $affectedSupplierIds = array_unique($affectedSupplierIds);
    
    echo "Supplier yang akan di-recalculate: " . implode(', ', $affectedSupplierIds) . "\n";
    
    // Delete produk (database will SET NULL automatically)
    $produk->delete();
    echo "✅ Produk berhasil dihapus\n\n";
    
    // Recalculate metrics
    $metricsService = app(\App\Services\Supplier\SupplierMetricsService::class);
    foreach ($affectedSupplierIds as $supplierId) {
        if ($supplierId) {
            $metricsService->recalculateForSupplier($supplierId);
            echo "✅ Kinerja supplier ID {$supplierId} berhasil di-recalculate\n";
        }
    }
    
    \DB::commit();
    echo "\n";
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// 3. Verifikasi setelah hapus
echo "--- SETELAH HAPUS ---\n";

$produkCheck = \App\Models\Produk::find(2);
echo "Produk ID 2: " . ($produkCheck ? "Masih ada (ERROR!)" : "✅ Sudah terhapus") . "\n\n";

$pengadaanCheck = \App\Models\Pengadaan::find(1);
echo "Pengadaan ID 1:\n";
echo "- Produk ID: " . ($pengadaanCheck->produk_id ?? '✅ NULL (sudah di-set NULL oleh database)') . "\n";
echo "- Supplier: " . $pengadaanCheck->supplier->nama . "\n\n";

$supplierCheck = \App\Models\Supplier::find(2);
echo "Kinerja Supplier PT ABC INDO SETELAH:\n";
echo "- Mean Hari Keterlambatan: {$supplierCheck->mean_hari_keterlambatan}\n";
echo "- Total Persen Cacat: {$supplierCheck->total_persen_cacat}\n";
echo "- Total Persen Keterlambatan: {$supplierCheck->total_persen_keterlambatan}\n\n";

echo "=== TEST SELESAI ===\n";
