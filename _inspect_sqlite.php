<?php
$path = __DIR__ . '/database/database.sqlite';
if (!file_exists($path)) { echo "NO_SQLITE_FILE\n"; exit; }
$pdo = new PDO('sqlite:' . $path);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if ($t === 'sqlite_sequence') continue;
    $count = $pdo->query("SELECT COUNT(*) FROM \"$t\"")->fetchColumn();
    echo str_pad($t, 30) . " : " . $count . "\n";
}
