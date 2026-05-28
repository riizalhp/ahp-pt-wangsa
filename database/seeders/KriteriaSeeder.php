<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;

class KriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['kode' => 'C', 'nama' => 'Cost', 'deskripsi' => 'Kriteria biaya pengadaan barang/produk.'],
            ['kode' => 'Q', 'nama' => 'Quality', 'deskripsi' => 'Kriteria kualitas produk yang diterima.'],
            ['kode' => 'D', 'nama' => 'Delivery', 'deskripsi' => 'Kriteria ketepatan waktu pengiriman barang.'],
            ['kode' => 'S', 'nama' => 'Service', 'deskripsi' => 'Kriteria pelayanan dan responsivitas supplier.'],
            ['kode' => 'R', 'nama' => 'Repair Service', 'deskripsi' => 'Kriteria penanganan klaim dan perbaikan barang cacat.'],
        ];

        foreach ($defaults as $item) {
            Kriteria::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
