<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  CROSSCHECK: Delete Produk → Update Kinerja Supplier        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ===================================================================
// STEP 1: TAMPILKAN DATA SEBELUM DELETE
// ===================================================================
echo "📊 STEP 1: DATA SEBELUM DELETE\n";
echo str_repeat("─", 65) . "\n\n";

echo "🔹 PRODUK YANG ADA:\n";
$produks = \App\Models\Produk::with('supplier')->get();
if ($produks->isEmpty()) {
    echo "   ⚠️  Tidak ada produk. Silakan tambah produk terlebih dahulu.\n\n";
} else {
    foreach ($produks as $p) {
        echo sprintf("   ID: %d | %-20s | Merk: %-10s | Supplier: %s\n", 
            $p->id, 
            $p->nama, 
            $p->merk,
            $p->supplier ? $p->supplier->nama : 'N/A'
        );
    }
    echo "\n";
}

echo "🔹 SUPPLIER & KINERJA SEKARANG:\n";
$suppliers = \App\Models\Supplier::all();
foreach ($suppliers as $s) {
    $totalPengadaan = \App\Models\Pengadaan::where('supplier_id', $s->id)
        ->whereNotNull('produk_id')
        ->count();
    
    echo sprintf("   ID: %d | %-25s\n", $s->id, $s->nama);
    echo sprintf("      • Total Pengadaan (produk tidak NULL): %d\n", $totalPengadaan);
    echo sprintf("      • Keterlambatan: %.2f%%\n", $s->total_persen_keterlambatan);
    echo sprintf("      • Cacat: %.2f%%\n", $s->total_persen_cacat);
    echo sprintf("      • Mean Hari Terlambat: %.2f hari\n", $s->mean_hari_keterlambatan);
    echo "\n";
}

echo "🔹 PENGADAAN:\n";
$pengadaans = \App\Models\Pengadaan::with('supplier', 'produk')->get();
if ($pengadaans->isEmpty()) {
    echo "   ⚠️  Tidak ada data pengadaan.\n\n";
} else {
    foreach ($pengadaans as $pg) {
        echo sprintf("   ID: %d | Produk: %-20s | Supplier: %s\n", 
            $pg->id,
            $pg->produk ? $pg->produk->nama . ' (ID: ' . $pg->produk_id . ')' : '⚠️  NULL',
            $pg->supplier->nama
        );
    }
    echo "\n";
}

// ===================================================================
// STEP 2: INSTRUKSI DELETE
// ===================================================================
echo str_repeat("═", 65) . "\n";
echo "🎯 STEP 2: INSTRUKSI DELETE PRODUK\n";
echo str_repeat("─", 65) . "\n\n";

if ($produks->isEmpty()) {
    echo "⚠️  Tidak bisa melanjutkan karena tidak ada produk.\n";
    echo "   Silakan tambah produk via browser terlebih dahulu.\n\n";
    exit;
}

$produkToDelete = $produks->first();
echo "📌 PRODUK YANG AKAN DIHAPUS:\n";
echo sprintf("   ID: %d\n", $produkToDelete->id);
echo sprintf("   Nama: %s\n", $produkToDelete->nama);
echo sprintf("   Merk: %s\n", $produkToDelete->merk);
echo sprintf("   Ukuran: %s\n", $produkToDelete->ukuran ?? 'N/A');
echo sprintf("   Supplier: %s (ID: %d)\n\n", 
    $produkToDelete->supplier->nama, 
    $produkToDelete->supplier_id
);

$pengadaanCount = \App\Models\Pengadaan::where('produk_id', $produkToDelete->id)->count();
$detailCount = \App\Models\PengadaanDetail::where('produk_id', $produkToDelete->id)->count();

echo "📊 DAMPAK DELETE:\n";
echo sprintf("   • Pengadaan yang menggunakan produk ini: %d record\n", $pengadaanCount);
echo sprintf("   • Pengadaan Detail yang menggunakan produk ini: %d record\n", $detailCount);
echo "\n";

if ($pengadaanCount > 0 || $detailCount > 0) {
    echo "   ✅ Setelah delete:\n";
    echo "      - Produk akan dihapus dari data_produk\n";
    echo "      - Pengadaan tetap ada, tapi produk_id = NULL (via SET NULL)\n";
    echo "      - Kinerja supplier akan di-recalculate\n";
} else {
    echo "   ℹ️  Produk ini tidak digunakan di pengadaan.\n";
    echo "      Kinerja tidak akan berubah karena tidak ada data yang terpengaruh.\n";
}

echo "\n";
echo str_repeat("═", 65) . "\n";
echo "🔥 STEP 3: DELETE VIA BROWSER\n";
echo str_repeat("─", 65) . "\n\n";

echo "Silakan buka browser dan lakukan:\n\n";
echo "1. Buka: http://127.0.0.1:8000/supervisor/produk\n";
echo "2. Cari produk: {$produkToDelete->nama} (ID: {$produkToDelete->id})\n";
echo "3. Klik tombol HAPUS (icon trash)\n";
echo "4. Konfirmasi hapus\n";
echo "5. Pesan yang muncul:\n";
echo "   ✅ 'Produk berhasil dihapus dan kinerja supplier telah diperbarui.'\n\n";

echo "Setelah itu, jalankan script ini lagi untuk lihat hasilnya:\n";
echo "   php crosscheck_delete.php verify\n\n";

// ===================================================================
// STEP 4: VERIFY (jika ada parameter 'verify')
// ===================================================================
if (isset($argv[1]) && $argv[1] === 'verify') {
    echo str_repeat("═", 65) . "\n";
    echo "✅ STEP 4: VERIFIKASI SETELAH DELETE\n";
    echo str_repeat("─", 65) . "\n\n";
    
    echo "🔹 PRODUK (cek apakah sudah terhapus):\n";
    $produkCheck = \App\Models\Produk::find($produkToDelete->id);
    if ($produkCheck) {
        echo "   ❌ ERROR! Produk ID {$produkToDelete->id} masih ada!\n\n";
    } else {
        echo "   ✅ Produk ID {$produkToDelete->id} BERHASIL DIHAPUS!\n\n";
    }
    
    echo "🔹 PENGADAAN (cek apakah produk_id = NULL):\n";
    $pengadaanCheck = \App\Models\Pengadaan::whereNull('produk_id')->get();
    if ($pengadaanCheck->isEmpty()) {
        echo "   ℹ️  Tidak ada pengadaan dengan produk_id = NULL\n\n";
    } else {
        echo "   ✅ Pengadaan dengan produk_id = NULL:\n";
        foreach ($pengadaanCheck as $pg) {
            echo sprintf("      ID: %d | Supplier: %s | produk_id: NULL\n", 
                $pg->id, 
                $pg->supplier->nama
            );
        }
        echo "\n";
    }
    
    echo "🔹 KINERJA SUPPLIER SETELAH DELETE:\n";
    $suppliersAfter = \App\Models\Supplier::all();
    foreach ($suppliersAfter as $s) {
        $totalPengadaanAfter = \App\Models\Pengadaan::where('supplier_id', $s->id)
            ->whereNotNull('produk_id')
            ->count();
        
        echo sprintf("   ID: %d | %-25s\n", $s->id, $s->nama);
        echo sprintf("      • Total Pengadaan (produk tidak NULL): %d\n", $totalPengadaanAfter);
        echo sprintf("      • Keterlambatan: %.2f%%\n", $s->total_persen_keterlambatan);
        echo sprintf("      • Cacat: %.2f%%\n", $s->total_persen_cacat);
        echo sprintf("      • Mean Hari Terlambat: %.2f hari\n", $s->mean_hari_keterlambatan);
        
        // Compare
        $supplierBefore = $suppliers->firstWhere('id', $s->id);
        if ($supplierBefore) {
            $totalBefore = \App\Models\Pengadaan::where('supplier_id', $s->id)
                ->whereNotNull('produk_id')
                ->count();
            
            if ($totalPengadaanAfter != $totalBefore) {
                echo sprintf("      ⚠️  PERUBAHAN: Total pengadaan berubah dari %d → %d\n", 
                    $totalBefore, $totalPengadaanAfter);
            }
            
            if ($s->total_persen_keterlambatan != $supplierBefore->total_persen_keterlambatan) {
                echo sprintf("      ⚠️  PERUBAHAN: Keterlambatan berubah dari %.2f%% → %.2f%%\n", 
                    $supplierBefore->total_persen_keterlambatan,
                    $s->total_persen_keterlambatan);
            }
        }
        echo "\n";
    }
    
    echo str_repeat("═", 65) . "\n";
    echo "✅ CROSSCHECK SELESAI!\n\n";
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Script selesai. Ikuti instruksi di atas.                   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";
