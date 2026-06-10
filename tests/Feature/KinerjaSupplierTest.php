<?php

namespace Tests\Feature;

use App\Models\PengadaanDetail;
use App\Models\PengadaanHeader;
use App\Models\Produk;
use App\Models\Supplier;
use App\Services\Supplier\SupplierMetricsService;
use Eris\Generators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PropertyTestCase;

/**
 * Feature property test for Kinerja Supplier inclusion.
 *
 * Feature: procurement-supplier-management
 * Property 14: Kinerja Supplier includes exactly the suppliers with received items.
 * Validates: Requirement 13.2
 */
class KinerjaSupplierTest extends PropertyTestCase
{
    use RefreshDatabase;

    public function testIncludesExactlySuppliersWithAtLeastOneReceivedDetail(): void
    {
        $this->forAll(
            // For each of 4 suppliers: 0 = no PO, 1 = PO with unreceived detail, 2 = PO with received detail.
            Generators::vector(4, Generators::choose(0, 2))
        )->then(function (array $states) {
            // Reset tables each iteration.
            PengadaanDetail::query()->delete();
            PengadaanHeader::query()->delete();
            Produk::query()->delete();
            Supplier::query()->delete();

            $expectedWithPerformance = [];

            foreach ($states as $i => $state) {
                $supplier = Supplier::create(['kode' => 'SUP-' . $i, 'nama' => 'Supplier ' . $i]);
                $produk = Produk::create([
                    'kode' => 'PRD-' . $i, 'nama' => 'P' . $i, 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $supplier->id,
                ]);

                if ($state === 0) {
                    continue; // no PO at all
                }

                $header = PengadaanHeader::create([
                    'supplier_id' => $supplier->id, 'no_po' => 'PO-' . $i,
                    'tanggal_po' => '2026-01-01', 'tanggal_kedatangan_target' => '2026-01-10',
                ]);

                if ($state === 1) {
                    // Unreceived detail (nulls).
                    PengadaanDetail::create([
                        'pengadaan_id' => $header->id, 'produk_id' => $produk->id,
                        'jumlah_dipesan' => 10, 'satuan' => 'pcs',
                    ]);
                } else {
                    // Received detail.
                    PengadaanDetail::create([
                        'pengadaan_id' => $header->id, 'produk_id' => $produk->id,
                        'jumlah_dipesan' => 10, 'satuan' => 'pcs',
                        'jumlah_diterima_baik' => 8, 'tanggal_kedatangan_aktual' => '2026-01-12',
                        'persen_kualitas_item' => 80, 'hari_keterlambatan' => 2,
                    ]);
                    $expectedWithPerformance[] = $supplier->id;
                }
            }

            $resultIds = app(SupplierMetricsService::class)
                ->suppliersWithPerformance()
                ->pluck('id')
                ->sort()
                ->values()
                ->all();

            sort($expectedWithPerformance);

            $this->assertSame($expectedWithPerformance, $resultIds);
        });
    }
}
