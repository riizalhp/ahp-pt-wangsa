<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$cols = Schema::getColumnListing('data_produk');
echo 'COLUMNS: ' . implode(', ', $cols) . PHP_EOL;

$expected = ['jenis_produk', 'merk', 'ukuran', 'kapasitas_pasokan', 'supplier_id'];
foreach ($expected as $c) {
    echo "  has {$c}: " . (in_array($c, $cols, true) ? 'YES' : 'NO') . PHP_EOL;
}

$fks = DB::select('PRAGMA foreign_key_list(data_produk)');
echo 'FOREIGN KEYS: ' . json_encode($fks, JSON_PRETTY_PRINT) . PHP_EOL;

$idx = DB::select('PRAGMA index_list(data_produk)');
echo 'INDEX LIST: ' . json_encode($idx, JSON_PRETTY_PRINT) . PHP_EOL;
