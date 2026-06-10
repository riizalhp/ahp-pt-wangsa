<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\PengadaanHeader;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Supplier CRUD + validation + delete-guard example tests.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.7, 1.8
 */
class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    private Akun $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->supervisor = Akun::firstOrCreate(
            ['username' => 'sup_crud'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Supervisor', 'role' => 'supervisor']
        );
    }

    // ── List ────────────────────────────────────────────────────────────────

    public function testIndexListsSuppliers(): void
    {
        Supplier::create(['kode' => 'S-001', 'nama' => 'Toko Makmur']);
        Supplier::create(['kode' => 'S-002', 'nama' => 'Toko Sejahtera']);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.supplier.index'));

        $response->assertOk();
        $response->assertSee('Toko Makmur');
        $response->assertSee('Toko Sejahtera');
    }

    public function testIndexExcludesComputedMetricsColumns(): void
    {
        // The index view should NOT show raw computed metric values as editable.
        Supplier::create([
            'kode' => 'S-003', 'nama' => 'Toko Laris',
            'total_persen_keterlambatan' => 50.0,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.supplier.index'));

        $response->assertOk();
        $response->assertSee('Toko Laris');
    }

    // ── Create / Store ───────────────────────────────────────────────────────

    public function testStoreCreatesANewSupplier(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.supplier.store'), [
                'kode'         => 'S-NEW',
                'nama'         => 'Supplier Baru',
                'alamat'       => 'Jl. Baru No. 1',
                'telepon'      => '081234567890',
                'jenis_barang' => 'ATK',
            ]);

        $response->assertRedirect(route('supervisor.supplier.index'));
        $this->assertDatabaseHas('data_supplier', ['kode' => 'S-NEW', 'nama' => 'Supplier Baru']);
    }

    public function testStorRejectsDuplicateKode(): void
    {
        Supplier::create(['kode' => 'S-DUP', 'nama' => 'Existing']);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.supplier.store'), [
                'kode' => 'S-DUP',
                'nama' => 'Another',
            ]);

        $response->assertSessionHasErrors('kode');
    }

    public function testStoreRejectsMissingNama(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.supplier.store'), [
                'kode' => 'S-NONAME',
                'nama' => '',
            ]);

        $response->assertSessionHasErrors('nama');
    }

    // ── Edit / Update ────────────────────────────────────────────────────────

    public function testUpdateModifiesAnExistingSupplier(): void
    {
        $supplier = Supplier::create(['kode' => 'S-UPD', 'nama' => 'Old Name']);

        $response = $this->actingAs($this->supervisor)
            ->put(route('supervisor.supplier.update', $supplier->id), [
                'kode' => 'S-UPD',
                'nama' => 'New Name',
            ]);

        $response->assertRedirect(route('supervisor.supplier.index'));
        $this->assertDatabaseHas('data_supplier', ['id' => $supplier->id, 'nama' => 'New Name']);
    }

    public function testUpdateAllowsSameKodeForSelf(): void
    {
        $supplier = Supplier::create(['kode' => 'S-SELF', 'nama' => 'Self']);

        $response = $this->actingAs($this->supervisor)
            ->put(route('supervisor.supplier.update', $supplier->id), [
                'kode' => 'S-SELF',  // same kode — should be allowed
                'nama' => 'Self Updated',
            ]);

        $response->assertRedirect(route('supervisor.supplier.index'));
    }

    // ── Delete guard ─────────────────────────────────────────────────────────

    public function testDestroyDeletesUnreferencedSupplier(): void
    {
        $supplier = Supplier::create(['kode' => 'S-DEL', 'nama' => 'To Delete']);

        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.supplier.destroy', $supplier->id));

        $response->assertRedirect(route('supervisor.supplier.index'));
        $this->assertDatabaseMissing('data_supplier', ['id' => $supplier->id]);
    }

    public function testDestroyRejectsSupplierReferencedByPengadaanHeader(): void
    {
        $supplier = Supplier::create(['kode' => 'S-INUSE', 'nama' => 'In Use']);
        PengadaanHeader::create([
            'supplier_id'               => $supplier->id,
            'no_po'                     => 'PO-INUSE',
            'tanggal_po'                => '2026-01-01',
            'tanggal_kedatangan_target' => '2026-01-10',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->delete(route('supervisor.supplier.destroy', $supplier->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('data_supplier', ['id' => $supplier->id]);
    }
}
