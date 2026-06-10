<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Schema-presence example test.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 15.1, 15.2
 */
class SchemaPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function testPengadaanHeaderTableHasRequiredColumns(): void
    {
        $this->assertTrue(Schema::hasTable('data_pengadaan_header'));

        $this->assertTrue(Schema::hasColumns('data_pengadaan_header', [
            'id', 'supplier_id', 'no_po', 'tanggal_po', 'tanggal_kedatangan_target', 'catatan',
        ]));
    }

    public function testPengadaanDetailTableHasRequiredColumns(): void
    {
        $this->assertTrue(Schema::hasTable('data_pengadaan_detail'));

        $this->assertTrue(Schema::hasColumns('data_pengadaan_detail', [
            'id', 'pengadaan_id', 'produk_id', 'jumlah_dipesan', 'satuan',
            'jumlah_diterima_baik', 'tanggal_kedatangan_aktual', 'persen_kualitas_item', 'hari_keterlambatan',
        ]));
    }
}
