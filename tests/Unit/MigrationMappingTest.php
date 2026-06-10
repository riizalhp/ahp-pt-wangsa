<?php

namespace Tests\Unit;

use App\Support\MigrationMapper;
use Eris\Generators;
use Tests\PropertyTestCase;

/**
 * Property-based test for the pure MigrationMapper helper.
 *
 * Feature: procurement-supplier-management
 * Property 15: Data migration preserves order and receiving values.
 * Validates: Requirement 15.4
 */
class MigrationMappingTest extends PropertyTestCase
{
    public function testMappedHeaderAndDetailReconstructLegacyRowWithoutLoss(): void
    {
        $this->forAll(
            Generators::choose(1, 100000),   // id
            Generators::choose(1, 9999),     // supplier_id
            Generators::choose(1, 9999),     // produk_id
            Generators::choose(0, 100000),   // jumlah_dibeli
            Generators::choose(0, 100000),   // jumlah_diterima
            Generators::choose(0, 100000),   // jumlah_cacat
            Generators::choose(0, 100)       // hari_keterlambatan
        )->then(function (
            int $id,
            int $supplierId,
            int $produkId,
            int $dibeli,
            int $diterima,
            int $cacat,
            int $hari
        ) {
            $satuan = 'pcs';
            $row = [
                'id'                 => $id,
                'supplier_id'        => $supplierId,
                'produk_id'          => $produkId,
                'jumlah_dibeli'      => $dibeli,
                'tanggal_po'         => '2026-01-10',
                'tanggal_kedatangan' => '2026-01-20',
                'jumlah_diterima'    => $diterima,
                'jumlah_cacat'       => $cacat,
                'persen_kualitas'    => 87.5,
                'hari_keterlambatan' => $hari,
                'catatan'            => 'legacy note',
            ];

            $mapped = MigrationMapper::mapFlatRow($row, $satuan);
            $header = $mapped['header'];
            $detail = $mapped['detail'];

            // Supplier preserved on header.
            $this->assertSame($supplierId, $header['supplier_id']);

            // Synthesized unique no_po embeds the legacy id.
            $this->assertSame('PO/MIGRASI/' . $id, $header['no_po']);

            // Dates preserved: header target + detail actual both map from tanggal_kedatangan.
            $this->assertSame('2026-01-10', $header['tanggal_po']);
            $this->assertSame('2026-01-20', $header['tanggal_kedatangan_target']);
            $this->assertSame('2026-01-20', $detail['tanggal_kedatangan_aktual']);
            $this->assertSame('legacy note', $header['catatan']);

            // Ordered quantity preserved as decimal.
            $this->assertSame((float) $dibeli, $detail['jumlah_dipesan']);
            $this->assertSame($produkId, $detail['produk_id']);
            $this->assertSame($satuan, $detail['satuan']);

            // Good received = diterima - cacat, clamped at 0.
            $this->assertSame(max(0.0, (float) $diterima - (float) $cacat), $detail['jumlah_diterima_baik']);

            // Per-item quality and lateness carried over.
            $this->assertSame(87.5, $detail['persen_kualitas_item']);
            $this->assertSame($hari, $detail['hari_keterlambatan']);
        });
    }

    /**
     * A null received quantity maps to a null good-received value (goods not
     * yet received), independent of any defective count.
     * Validates: Requirement 15.4
     */
    public function testNullReceivedMapsToNullGoodReceived(): void
    {
        $this->forAll(
            Generators::choose(1, 100000),
            Generators::choose(0, 100000)
        )->then(function (int $id, int $cacat) {
            $row = [
                'id'                 => $id,
                'supplier_id'        => 1,
                'produk_id'          => 1,
                'jumlah_dibeli'      => 10,
                'tanggal_po'         => '2026-01-10',
                'tanggal_kedatangan' => '2026-01-20',
                'jumlah_diterima'    => null,
                'jumlah_cacat'       => $cacat,
                'persen_kualitas'    => null,
                'hari_keterlambatan' => null,
                'catatan'            => null,
            ];

            $detail = MigrationMapper::mapFlatRow($row, 'pcs')['detail'];

            $this->assertNull($detail['jumlah_diterima_baik']);
        });
    }
}
