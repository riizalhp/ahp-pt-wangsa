<?php

require __DIR__ . '/vendor/autoload.php';

use App\Support\MigrationMapper;

$row = [
    'id' => 7,
    'supplier_id' => 3,
    'produk_id' => 5,
    'jumlah_dibeli' => 10,
    'tanggal_po' => '2026-01-01',
    'tanggal_kedatangan' => '2026-01-05',
    'jumlah_diterima' => 10,
    'jumlah_cacat' => 2,
    'persen_kualitas' => 80.0,
    'hari_keterlambatan' => 3,
    'catatan' => 'catatan lama',
    'satuan' => 'pcs',
];

echo json_encode(MigrationMapper::mapFlatRow($row), JSON_PRETTY_PRINT) . PHP_EOL;

// Edge case: goods not received yet (jumlah_diterima null)
$row2 = $row;
$row2['jumlah_diterima'] = null;
$row2['jumlah_cacat'] = null;
echo json_encode(MigrationMapper::mapFlatRow($row2)['detail'], JSON_PRETTY_PRINT) . PHP_EOL;
