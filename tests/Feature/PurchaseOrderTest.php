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
 * Feature property tests for the Administrator Purchasing Purchase Order module.
 *
 * Feature: procurement-supplier-management
 */
class PurchaseOrderTest extends PropertyTestCase
{
    use RefreshDatabase;

    private Akun $sales;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sales = Akun::firstOrCreate(
            ['username' => 'sales1'],
            [
                'password_hash' => bcrypt('secret'),
                'nama'          => 'Sales Staff',
                'role'          => 'sales',
            ]
        );

        $this->supplier = Supplier::firstOrCreate(
            ['kode' => 'SUP-1'],
            ['nama' => 'Supplier Satu']
        );
    }

    private function makeProduk(int $n): Produk
    {
        return Produk::create([
            'kode'        => 'PRD-' . $n,
            'nama'        => 'Produk ' . $n,
            'satuan'      => 'pcs',
            'harga'       => 1000,
            'supplier_id' => $this->supplier->id,
        ]);
    }

    /**
     * Property 2: Purchase Order assembly creates one header and one detail per line item.
     * Validates: Requirements 3.1, 3.2, 15.3
     */
    public function testPoAssemblyCreatesOneHeaderAndOneDetailPerLineItem(): void
    {
        $this->forAll(
            Generators::choose(1, 5)
        )->then(function (int $itemCount) {
            // Clean slate per iteration.
            PengadaanDetail::query()->delete();
            PengadaanHeader::query()->delete();
            Produk::query()->delete();

            $produks = [];
            for ($i = 1; $i <= $itemCount; $i++) {
                $produks[] = $this->makeProduk($i);
            }

            $items = [];
            foreach ($produks as $idx => $p) {
                $items[] = [
                    'produk_id'      => $p->id,
                    'jumlah_dipesan' => $idx + 1,
                    'satuan'         => 'pcs',
                ];
            }

            $noPo = 'PO-' . uniqid();

            $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
                'supplier_id'               => $this->supplier->id,
                'no_po'                     => $noPo,
                'tanggal_po'                => '2026-01-10',
                'tanggal_kedatangan_target' => '2026-01-20',
                'items'                     => $items,
            ]);

            $response->assertRedirect(route('sales.purchase_order.index'));

            // Exactly one header.
            $this->assertSame(1, PengadaanHeader::where('no_po', $noPo)->count());
            $header = PengadaanHeader::where('no_po', $noPo)->first();

            // Exactly one detail per line item.
            $this->assertSame($itemCount, PengadaanDetail::where('pengadaan_id', $header->id)->count());
        });
    }

    /**
     * Property 3: Satuan accepts predefined and free-text values.
     * Validates: Requirement 3.4
     */
    public function testSatuanAcceptsPredefinedAndFreeTextValues(): void
    {
        $this->forAll(
            Generators::oneOf(
                Generators::elements(['Rim', 'Pcs', 'Ltr', 'Lbr', 'Kg', 'Pack', 'Roll']),
                Generators::elements(['galon', 'sak', 'unit-khusus', 'dus besar'])
            )
        )->then(function (string $satuan) {
            PengadaanDetail::query()->delete();
            PengadaanHeader::query()->delete();
            Produk::query()->delete();

            $produk = $this->makeProduk(1);
            $noPo = 'PO-' . uniqid();

            $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
                'supplier_id'               => $this->supplier->id,
                'no_po'                     => $noPo,
                'tanggal_po'                => '2026-01-10',
                'tanggal_kedatangan_target' => '2026-01-20',
                'items'                     => [[
                    'produk_id'      => $produk->id,
                    'jumlah_dipesan' => 5,
                    'satuan'         => $satuan,
                ]],
            ]);

            $response->assertRedirect(route('sales.purchase_order.index'));
            $this->assertDatabaseHas('data_pengadaan_detail', ['satuan' => $satuan]);
        });
    }

    /**
     * Property 4: Target arrival date ordering is enforced.
     * Validates: Requirement 3.7
     */
    public function testTargetArrivalDateOrderingIsEnforced(): void
    {
        $this->forAll(
            Generators::choose(1, 60)
        )->then(function (int $daysBefore) {
            $produk = Produk::first() ?? $this->makeProduk(1);

            $tanggalPo = new \DateTimeImmutable('2026-03-01');
            $target = $tanggalPo->modify("-{$daysBefore} days"); // strictly before PO date

            $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
                'supplier_id'               => $this->supplier->id,
                'no_po'                     => 'PO-' . uniqid(),
                'tanggal_po'                => $tanggalPo->format('Y-m-d'),
                'tanggal_kedatangan_target' => $target->format('Y-m-d'),
                'items'                     => [[
                    'produk_id'      => $produk->id,
                    'jumlah_dipesan' => 5,
                    'satuan'         => 'pcs',
                ]],
            ]);

            $response->assertSessionHasErrors('tanggal_kedatangan_target');
        });
    }

    /**
     * Example: a PO with no line items is rejected.
     * Validates: Requirement 3.3
     */
    public function testPoWithNoLineItemsIsRejected(): void
    {
        $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-EMPTY',
            'tanggal_po'                => '2026-01-10',
            'tanggal_kedatangan_target' => '2026-01-20',
            // no items
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('data_pengadaan_header', ['no_po' => 'PO-EMPTY']);
    }

    /**
     * Example: a duplicate No_PO is rejected.
     * Validates: Requirement 3.6
     */
    public function testDuplicateNoPoIsRejected(): void
    {
        $produk = $this->makeProduk(1);

        PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-DUP',
            'tanggal_po'                => '2026-01-10',
            'tanggal_kedatangan_target' => '2026-01-20',
        ]);

        $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-DUP',
            'tanggal_po'                => '2026-02-10',
            'tanggal_kedatangan_target' => '2026-02-20',
            'items'                     => [[
                'produk_id'      => $produk->id,
                'jumlah_dipesan' => 5,
                'satuan'         => 'pcs',
            ]],
        ]);

        $response->assertSessionHasErrors('no_po');
    }

    /**
     * Example: an invalid jumlah_dipesan (zero/negative) is rejected.
     * Validates: Requirement 3.5
     */
    public function testInvalidJumlahDipesanIsRejected(): void
    {
        $produk = $this->makeProduk(1);

        $response = $this->actingAs($this->sales)->post(route('sales.purchase_order.store'), [
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-INVALID-QTY',
            'tanggal_po'                => '2026-01-10',
            'tanggal_kedatangan_target' => '2026-01-20',
            'items'                     => [[
                'produk_id'      => $produk->id,
                'jumlah_dipesan' => 0,
                'satuan'         => 'pcs',
            ]],
        ]);

        $response->assertSessionHasErrors('items.0.jumlah_dipesan');
    }
}
