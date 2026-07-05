<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "🔧 SETUP TEST DATA: Pengadaan dengan Kinerja\n";
echo str_repeat("═", 65) . "\n\n";

// Get existing produk dan supplier
$produk1 = \App\Models\Produk::find(3); // KERTAS NIGGA
$produk2 = \App\Models\Produk::find(4); // KERTAS

if (!$produk1 || !$produk2) {
    echo "❌ Produk tidak ditemukan. Pastikan ada produk ID 3 dan 4.\n\n";
    exit;
}

$supplier1 = $produk1->supplier; // PT NIGGA
$supplier2 = $produk2->supplier; // PT KIMBEK

echo "📦 Produk 1: {$produk1->nama} (ID: {$produk1->id}) - Supplier: {$supplier1->nama}\n";
echo "📦 Produk 2: {$produk2->nama} (ID: {$produk2->id}) - Supplier: {$supplier2->nama}\n\n";

// Buat pengadaan untuk supplier1 (PT NIGGA) dengan produk1
echo "➕ Membuat pengadaan untuk {$supplier1->nama}...\n";

// Pengadaan 1: Tepat waktu, kualitas bagus
$p1 = \App\Models\Pengadaan::create([
    'supplier_id' => $supplier1->id,
    'produk_id' => $produk1->id,
    'jumlah_dibeli' => 100,
    'tanggal_po' => now()->subDays(10),
    'tanggal_kedatangan' => now()->subDays(8), // Tepat waktu (2 hari lebih cepat)
    'jumlah_diterima' => 100,
    'jumlah_cacat' => 0, // 0% cacat
    'persen_kualitas' => 100,
    'hari_keterlambatan' => -2, // Lebih cepat
    'catatan' => 'Test data - tepat waktu'
]);
echo "   ✅ Pengadaan ID {$p1->id}: Tepat waktu, 0% cacat\n";

// Pengadaan 2: Terlambat 5 hari, ada cacat 10%
$p2 = \App\Models\Pengadaan::create([
    'supplier_id' => $supplier1->id,
    'produk_id' => $produk1->id,
    'jumlah_dibeli' => 200,
    'tanggal_po' => now()->subDays(20),
    'tanggal_kedatangan' => now()->subDays(10), // Terlambat 5 hari
    'jumlah_diterima' => 200,
    'jumlah_cacat' => 20, // 10% cacat
    'persen_kualitas' => 90,
    'hari_keterlambatan' => 5,
    'catatan' => 'Test data - terlambat + cacat'
]);
echo "   ✅ Pengadaan ID {$p2->id}: Terlambat 5 hari, 10% cacat\n\n";

// Recalculate kinerja supplier1
echo "🔄 Recalculate kinerja {$supplier1->nama}...\n";
$metricsService = app(\App\Services\Supplier\SupplierMetricsService::class);
$metricsService->recalculateForSupplier($supplier1->id);

$supplier1->refresh();
echo "   📊 Hasil:\n";
echo "      • Total Persen Keterlambatan: {$supplier1->total_persen_keterlambatan}%\n";
echo "      • Mean Hari Keterlambatan: {$supplier1->mean_hari_keterlambatan} hari\n";
echo "      • Total Persen Cacat: {$supplier1->total_persen_cacat}%\n\n";

// Buat pengadaan untuk supplier2 (PT KIMBEK) dengan produk2
echo "➕ Membuat pengadaan untuk {$supplier2->nama}...\n";

// Pengadaan 3: Tepat waktu, kualitas perfect
$p3 = \App\Models\Pengadaan::create([
    'supplier_id' => $supplier2->id,
    'produk_id' => $produk2->id,
    'jumlah_dibeli' => 150,
    'tanggal_po' => now()->subDays(15),
    'tanggal_kedatangan' => now()->subDays(13),
    'jumlah_diterima' => 150,
    'jumlah_cacat' => 0,
    'persen_kualitas' => 100,
    'hari_keterlambatan' => 0,
    'catatan' => 'Test data - perfect'
]);
echo "   ✅ Pengadaan ID {$p3->id}: Tepat waktu, 0% cacat\n\n";

echo "🔄 Recalculate kinerja {$supplier2->nama}...\n";
$metricsService->recalculateForSupplier($supplier2->id);

$supplier2->refresh();
echo "   📊 Hasil:\n";
echo "      • Total Persen Keterlambatan: {$supplier2->total_persen_keterlambatan}%\n";
echo "      • Mean Hari Keterlambatan: {$supplier2->mean_hari_keterlambatan} hari\n";
echo "      • Total Persen Cacat: {$supplier2->total_persen_cacat}%\n\n";

echo str_repeat("═", 65) . "\n";
echo "✅ SETUP SELESAI!\n\n";

echo "Sekarang test delete:\n";
echo "1. Jalankan: php crosscheck_delete.php\n";
echo "2. Delete produk via browser sesuai instruksi\n";
echo "3. Jalankan: php crosscheck_delete.php verify\n\n";
