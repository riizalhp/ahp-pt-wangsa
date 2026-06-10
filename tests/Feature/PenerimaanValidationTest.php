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
 * Feature property test for goods-receiving date validation.
 *
 * Feature: procurement-supplier-management
 * Property 6: Actual arrival date ordering is enforced.
 * Validates: Requirement 4.5
 */
class PenerimaanValidationTest extends PropertyTestCase
{
    use RefreshDatabase;

    private Akun $logistik;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logistik = Akun::firstOrCreate(
            ['username' => 'log1'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Logistik', 'role' => 'logistik']
        );

        $this->supplier = Supplier::firstOrCreate(['kode' => 'SUP-1'], ['nama' => 'Supplier']);
    }

    public function testActualArrivalDateBeforePoDateIsRejected(): void
    {
        $produk = Produk::firstOrCreate(
            ['kode' => 'PRD-1'],
            ['nama' => 'Produk', 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $this->supplier->id]
        );

        $this->forAll(
            Generators::choose(1, 60)
        )->then(function (int $daysBefore) use ($produk) {
            $tanggalPo = new \DateTimeImmutable('2026-03-01');
            $aktual = $tanggalPo->modify("-{$daysBefore} days"); // strictly before PO date

            $header = PengadaanHeader::create([
                'supplier_id'               => $this->supplier->id,
                'no_po'                     => 'PO-' . uniqid(),
                'tanggal_po'                => $tanggalPo->format('Y-m-d'),
                'tanggal_kedatangan_target' => '2026-03-10',
            ]);

            $detail = PengadaanDetail::create([
                'pengadaan_id'   => $header->id,
                'produk_id'      => $produk->id,
                'jumlah_dipesan' => 10,
                'satuan'         => 'pcs',
            ]);

            $response = $this->actingAs($this->logistik)->put(
                route('logistik.penerimaan.update', $header->id),
                [
                    'items' => [
                        (string) $detail->id => [
                            'jumlah_diterima_baik'      => 5,
                            'tanggal_kedatangan_aktual' => $aktual->format('Y-m-d'),
                        ],
                    ],
                ]
            );

            // Controller flashes an error and does not persist the receiving values.
            $response->assertSessionHas('error');
            $detail->refresh();
            $this->assertNull($detail->jumlah_diterima_baik);
        });
    }
}
