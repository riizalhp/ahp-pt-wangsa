<?php

namespace Tests\Feature;

use App\Models\PengadaanDetail;
use App\Models\PengadaanHeader;
use App\Models\Produk;
use App\Models\Supplier;
use App\Services\Performance\PerformanceCalculator;
use App\Services\Supplier\SupplierMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit/integration test for SupplierMetricsService aggregation.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 7.1, 8.1, 8.2, 9.1, 9.2
 */
class SupplierMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testAggregatesPersistedMatchCalculatorAcrossDistinctPos(): void
    {
        $supplier = Supplier::create(['kode' => 'SUP-1', 'nama' => 'Supplier']);
        $produk = Produk::create([
            'kode' => 'PRD-1', 'nama' => 'Produk', 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $supplier->id,
        ]);

        // Three received details across two distinct POs.
        // PO1: two items (one late 4 days @80% quality, one on-time @100%).
        // PO2: one item (late 2 days @ 50% quality).
        $po1 = PengadaanHeader::create([
            'supplier_id' => $supplier->id, 'no_po' => 'PO-1',
            'tanggal_po' => '2026-01-01', 'tanggal_kedatangan_target' => '2026-01-10',
        ]);
        $po2 = PengadaanHeader::create([
            'supplier_id' => $supplier->id, 'no_po' => 'PO-2',
            'tanggal_po' => '2026-02-01', 'tanggal_kedatangan_target' => '2026-02-10',
        ]);

        $details = [
            ['header' => $po1, 'hari' => 4, 'kualitas' => 80.0],
            ['header' => $po1, 'hari' => 0, 'kualitas' => 100.0],
            ['header' => $po2, 'hari' => 2, 'kualitas' => 50.0],
        ];

        foreach ($details as $d) {
            PengadaanDetail::create([
                'pengadaan_id'              => $d['header']->id,
                'produk_id'                 => $produk->id,
                'jumlah_dipesan'            => 10,
                'satuan'                    => 'pcs',
                'jumlah_diterima_baik'      => 8,
                'tanggal_kedatangan_aktual' => '2026-01-15',
                'persen_kualitas_item'      => $d['kualitas'],
                'hari_keterlambatan'        => $d['hari'],
            ]);
        }

        $service = app(SupplierMetricsService::class);
        $service->recalculateForSupplier($supplier->id);

        $supplier->refresh();

        $calc = new PerformanceCalculator();

        // Expected lateness percentage: 2 of 3 items late -> 66.67%.
        $expectedLatePct = $calc->persenKeterlambatanSupplier([4, 0, 2]);
        // Expected mean lateness per distinct PO: (4+0+2)/2 = 3.0.
        $expectedMean = $calc->meanHariKeterlambatan([
            ['pengadaan_id' => $po1->id, 'hari_keterlambatan' => 4],
            ['pengadaan_id' => $po1->id, 'hari_keterlambatan' => 0],
            ['pengadaan_id' => $po2->id, 'hari_keterlambatan' => 2],
        ]);
        // Expected cumulative defect: 100 - mean(80,100,50).
        $expectedCacat = $calc->totalPersenCacatSupplier($calc->persenKualitasKumulatif([80.0, 100.0, 50.0]));

        $this->assertEqualsWithDelta($expectedLatePct, (float) $supplier->total_persen_keterlambatan, 1e-6);
        $this->assertEqualsWithDelta(3.0, (float) $supplier->mean_hari_keterlambatan, 1e-6);
        $this->assertEqualsWithDelta($expectedMean, (float) $supplier->mean_hari_keterlambatan, 1e-6);
        $this->assertEqualsWithDelta($expectedCacat, (float) $supplier->total_persen_cacat, 1e-6);
    }

    public function testEmptyReceivedDetailsForceAllAggregatesToZero(): void
    {
        $supplier = Supplier::create([
            'kode' => 'SUP-EMPTY', 'nama' => 'Empty',
            'total_persen_keterlambatan' => 50, 'mean_hari_keterlambatan' => 5, 'total_persen_cacat' => 20,
        ]);

        app(SupplierMetricsService::class)->recalculateForSupplier($supplier->id);
        $supplier->refresh();

        $this->assertSame(0.0, (float) $supplier->total_persen_keterlambatan);
        $this->assertSame(0.0, (float) $supplier->mean_hari_keterlambatan);
        $this->assertSame(0.0, (float) $supplier->total_persen_cacat);
    }
}
