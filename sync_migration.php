<?php
// Skrip sementara: menyelaraskan catatan tabel `migrations` di MySQL.
// Tabel spk_supplier sudah ada + berisi data, tapi record migrasinya belum tercatat.

$my = new PDO('mysql:host=127.0.0.1;port=3306;dbname=spk_supplier', 'root', '');
$my->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Isi tabel migrations saat ini ===\n";
$rows = $my->query("SELECT id, migration, batch FROM migrations ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "[{$r['batch']}] {$r['migration']}\n";
}

$target = '2026_05_28_084300_create_spk_supplier_tables';

$exists = $my->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
$exists->execute([$target]);

if ($exists->fetchColumn() > 0) {
    echo "\nRecord '$target' sudah ada. Tidak ada perubahan.\n";
} else {
    $batch = (int) $my->query("SELECT COALESCE(MAX(batch),0) FROM migrations")->fetchColumn();
    $ins = $my->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
    $ins->execute([$target, $batch]);
    echo "\nRecord '$target' ditambahkan dengan batch $batch.\n";
}
