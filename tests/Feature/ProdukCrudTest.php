<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\PengadaanDetail;
use App\Models\PengadaanHeader;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Produk CRUD + validation + delete-guard example tests.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.8
 */
class ProdukCrudTest extends TestCase
{
    use RefreshDatabase;

    private Akun $supervisor;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = Akun::firstOrCreate(
            ['username' => 'sup_produk'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Supervisor', 'role' => 'supervisor']
        );

        $this->supplier = Supplier::create(['kode' => 'SUP-P', 'nama' => 'Supplier Produk']);
    }

    // ── List ────────────────────────────────────────────────────────────────

    public function testIndexListsProduks(): void
    {
        Produk::create([
            'kode' => 'P-001', 'nama' => 'Kertas A4', 'satuan' => 'rim', 'harga' => 50000,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.produk.index'));

        $response->assertOk();
        $response->assertSee('Kertas A4');
        $response->assertSee($this->supplier->nama);
    }

    // ── Create form ──────────────────────────────────────────────────────────

    public function testCreateFormContainsSupplierSelect(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.produk.create'));

        $response->assertOk();
        $response->assertSee($this->supplier->nama);
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function testStoreCreatesANewProduk(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.produk.store'), [
                'nama'        => 'Pulpen Hitam',
                'satuan'      => 'pcs',
                'harga'       => 5000,
                'supplier_id' => $this->supplier->id,
                'jenis_produk' => 'ATK',
            ]);

        $response->assertRedirect(route('supervisor.produk.index'));
        $this->assertDatabaseHas('data_produk', ['nama' => 'Pulpen Hitam']);
    }

    public function testStoreRejectsMissingNama(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.produk.store'), [
                'nama'        => '',
                'satuan'      => 'pcs',
                'harga'       => 5000,
                'supplier_id' => $this->supplier->id,
            ]);

        $response->assertSessionHasErrors('nama');
    }

    public function testStoreRejectsMissingSupplier(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.produk.store'), [
                'nama'   => 'Produk Tanpa Supplier',
                'satuan' => 'pcs',
                'harga'  => 1000,
            ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    public function testStoreRejectsNonExistentSupplierId(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.produk.store'), [
                'nama'        => 'Produk Ghost',
                'satuan'      => 'pcs',
                'harga'       => 1000,
                'supplier_id' => 99999,
            ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function testUpdateModifiesAnExistingProduk(): void
    {
        $produk = Produk::create([
            'kode' => 'P-UPD', 'nama' => 'Old Produk', 'satuan' => 'pcs', 'harga' => 1000,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->put(route('supervisor.produk.update', $produk->id), [
                'nama'        => 'Updated Produk',
                'satuan'      => 'pcs',
                'harga'       => 2000,
                'supplier_id' => $this->supplier->id,
            ]);

        $response->assertRedirect(route('supervisor.produk.index'));
        $this->assertDatabaseHas('data_produk', ['id' => $produk->id, 'nama' => 'Updated Produk']);
    }

    // ── Delete guard ─────────────────────────────────────────────────────────

    public function testDestroyDeletesUnreferencedProduk(): void
    {
        $produk = Produk::create([
            'kode' => 'P-DEL', 'nama' => 'To Delete', 'satuan' => 'pcs', 'harga' => 1,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.produk.destroy', $produk->id));

        $response->assertRedirect(route('supervisor.produk.index'));
        $this->assertDatabaseMissing('data_produk', ['id' => $produk->id]);
    }

    public function testDestroyRejectsProdukReferencedByPengadaanDetail(): void
    {
        $produk = Produk::create([
            'kode' => 'P-INUSE', 'nama' => 'In Use', 'satuan' => 'pcs', 'harga' => 1,
            'supplier_id' => $this->supplier->id,
        ]);

        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-PRDUSE',
            'tanggal_po'                => '2026-01-01',
            'tanggal_kedatangan_target' => '2026-01-10',
        ]);

        PengadaanDetail::create([
            'pengadaan_id'   => $header->id,
            'produk_id'      => $produk->id,
            'jumlah_dipesan' => 10,
            'satuan'         => 'pcs',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.produk.destroy', $produk->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('data_produk', ['id' => $produk->id]);
    }
}
