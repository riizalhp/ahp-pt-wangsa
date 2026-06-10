<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\PengadaanDetail;
use App\Models\PengadaanHeader;
use App\Models\Produk;
use App\Models\Supplier;
use Eris\Generators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PropertyTestCase;

/**
 * Feature property tests for the Logistik Goods Receiving module.
 *
 * Feature: procurement-supplier-management
 */
class PenerimaanTest extends PropertyTestCase
{
    use RefreshDatabase;

    private Akun $logistik;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logistik = Akun::firstOrCreate(
            ['username' => 'log1'],
            [
                'password_hash' => bcrypt('secret'),
                'nama'          => 'Logistik Staff',
                'role'          => 'logistik',
            ]
        );

        $this->supplier = Supplier::firstOrCreate(
            ['kode' => 'SUP-1'],
            ['nama' => 'Supplier Satu']
        );
    }

    /**
     * Create a fresh header with a single detail line and return [header, detail].
     *
     * @return array{0: PengadaanHeader, 1: PengadaanDetail}
     */
    private function makeHeaderWithDetail(float $jumlahDipesan): array
    {
        $produk = Produk::firstOrCreate(
            ['kode' => 'PRD-1'],
            ['nama' => 'Produk 1', 'satuan' => 'pcs', 'harga' => 1000, 'supplier_id' => $this->supplier->id]
        );

        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-' . uniqid(),
            'tanggal_po'                => '2026-01-10',
            'tanggal_kedatangan_target' => '2026-01-20',
        ]);

        $detail = PengadaanDetail::create([
            'pengadaan_id'   => $header->id,
            'produk_id'      => $produk->id,
            'jumlah_dipesan' => $jumlahDipesan,
            'satuan'         => 'pcs',
        ]);

        return [$header, $detail];
    }

    /**
     * Property 5: Received quantity cannot exceed ordered quantity.
     * Validates: Requirement 4.4
     */
    public function testReceivedQuantityCannotExceedOrderedQuantity(): void
    {
        $this->forAll(
            Generators::choose(1, 1000),   // ordered (x100)
            Generators::choose(1, 500)     // excess (x100)
        )->then(function (int $orderedRaw, int $excessRaw) {
            [$header, $detail] = $this->makeHeaderWithDetail($orderedRaw / 100.0);

            $over = ($orderedRaw + $excessRaw) / 100.0;

            $response = $this->actingAs($this->logistik)->put(
                route('logistik.penerimaan.update', $header->id),
                [
                    'items' => [
                        (string) $detail->id => [
                            'jumlah_diterima_baik'      => $over,
                            'tanggal_kedatangan_aktual' => '2026-01-22',
                        ],
                    ],
                ]
            );

            // The controller rejects with a flashed error and does not persist.
            $response->assertSessionHas('error');
            $detail->refresh();
            $this->assertNull($detail->jumlah_diterima_baik);
        });
    }

    /**
     * Property 8: Stored per-item quality round-trips.
     * Validates: Requirement 5.4
     */
    public function testStoredPerItemQualityRoundTrips(): void
    {
        $this->forAll(
            Generators::choose(1, 1000),   // ordered (x100)
            Generators::choose(0, 1000)    // received fraction numerator
        )->then(function (int $orderedRaw, int $recRaw) {
            $ordered = $orderedRaw / 100.0;
            [$header, $detail] = $this->makeHeaderWithDetail($ordered);

            // Received good <= ordered.
            $received = min($recRaw / 100.0, $ordered);

            $response = $this->actingAs($this->logistik)->put(
                route('logistik.penerimaan.update', $header->id),
                [
                    'items' => [
                        (string) $detail->id => [
                            'jumlah_diterima_baik'      => $received,
                            'tanggal_kedatangan_aktual' => '2026-01-22',
                        ],
                    ],
                ]
            );

            $response->assertRedirect(route('logistik.penerimaan.index'));

            $detail->refresh();

            $expectedQuality = ($received / $ordered) * 100.0;
            $this->assertEqualsWithDelta($expectedQuality, (float) $detail->persen_kualitas_item, 1e-6);
            $this->assertEqualsWithDelta($received, (float) $detail->jumlah_diterima_baik, 1e-6);
        });
    }

    /**
     * Example: loading a header loads all its details and persists across multiple lines.
     * Validates: Requirements 4.1, 4.2, 4.6
     */
    public function testReceivingPersistsAcrossMultipleLineItemsIndependently(): void
    {
        $produkA = Produk::firstOrCreate(['kode' => 'PRD-A'], ['nama' => 'A', 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $this->supplier->id]);
        $produkB = Produk::firstOrCreate(['kode' => 'PRD-B'], ['nama' => 'B', 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $this->supplier->id]);

        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-MULTI',
            'tanggal_po'                => '2026-01-10',
            'tanggal_kedatangan_target' => '2026-01-20',
        ]);

        $d1 = PengadaanDetail::create(['pengadaan_id' => $header->id, 'produk_id' => $produkA->id, 'jumlah_dipesan' => 100, 'satuan' => 'pcs']);
        $d2 = PengadaanDetail::create(['pengadaan_id' => $header->id, 'produk_id' => $produkB->id, 'jumlah_dipesan' => 50, 'satuan' => 'pcs']);

        $response = $this->actingAs($this->logistik)->put(
            route('logistik.penerimaan.update', $header->id),
            [
                'items' => [
                    (string) $d1->id => ['jumlah_diterima_baik' => 90, 'tanggal_kedatangan_aktual' => '2026-01-25'],
                    (string) $d2->id => ['jumlah_diterima_baik' => 50, 'tanggal_kedatangan_aktual' => '2026-01-18'],
                ],
            ]
        );

        $response->assertRedirect(route('logistik.penerimaan.index'));

        $d1->refresh();
        $d2->refresh();

        // d1: 90/100 = 90% quality, 5 days late (target 20 -> actual 25).
        $this->assertEqualsWithDelta(90.0, (float) $d1->persen_kualitas_item, 1e-6);
        $this->assertSame(5, (int) $d1->hari_keterlambatan);

        // d2: 50/50 = 100% quality, on time (actual 18 before target 20 -> 0 late).
        $this->assertEqualsWithDelta(100.0, (float) $d2->persen_kualitas_item, 1e-6);
        $this->assertSame(0, (int) $d2->hari_keterlambatan);
    }
}
