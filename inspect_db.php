<?php
$db = __DIR__ . '/database/database.sqlite';
if (!file_exists($db)) {
    echo "NO SQLITE FILE\n";
    exit;
}
echo "SQLITE FILE SIZE: " . filesize($db) . " bytes\n";
$pdo = new PDO('sqlite:' . $db);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
echo "TABLES: " . implode(', ', $tables) . "\n";
if (in_array('data_produk', $tables)) {
    echo "--- data_produk columns ---\n";
    foreach ($pdo->query("PRAGMA table_info(data_produk)") as $row) {
        echo "  {$row['name']} ({$row['type']})\n";
    }
}
if (in_array('migrations', $tables)) {
    echo "--- migrations applied ---\n";
    foreach ($pdo->query("SELECT migration, batch FROM migrations ORDER BY id") as $row) {
        echo "  {$row['migration']} (batch {$row['batch']})\n";
    }
}
