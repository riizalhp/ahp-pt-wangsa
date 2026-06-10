<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\Produk;
use App\Models\Supplier;
use Eris\Generators;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PropertyTestCase;

/**
 * Feature tests for AHP product-based alternative selection.
 *
 * Feature: procurement-supplier-management
 * Property 13: AHP alternatives are derived from the selected products' suppliers.
 * Validates: Requirements 10.2, 10.4, 10.5
 */
class AhpAlternativeTest extends PropertyTestCase
{
    use RefreshDatabase;

    private Akun $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = Akun::firstOrCreate(
            ['username' => 'sup1'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Supervisor', 'role' => 'supervisor']
        );
    }

    /**
     * Property 13: the distinct derived suppliers stored in the session are
     * exactly the suppliers of the selected products.
     */
    public function testDerivedSuppliersAreTheDistinctSuppliersOfSelectedProducts(): void
    {
        $this->forAll(
            // 3 suppliers; assign each of 4 products to one of them (index 0..2).
            Generators::vector(4, Generators::choose(0, 2))
        )->then(function (array $assignment) {
            Produk::query()->delete();
            Supplier::query()->delete();

            $suppliers = [];
            for ($i = 0; $i < 3; $i++) {
                $suppliers[$i] = Supplier::create(['kode' => 'SUP-' . $i . '-' . uniqid(), 'nama' => 'S' . $i]);
            }

            $produkIds = [];
            $expectedSupplierIds = [];
            foreach ($assignment as $idx => $supIndex) {
                $p = Produk::create([
                    'kode'        => 'PRD-' . $idx . '-' . uniqid(),
                    'nama'        => 'P' . $idx,
                    'satuan'      => 'pcs',
                    'harga'       => 1,
                    'supplier_id' => $suppliers[$supIndex]->id,
                ]);
                $produkIds[] = $p->id;
                $expectedSupplierIds[$suppliers[$supIndex]->id] = true;
            }

            $expected = array_keys($expectedSupplierIds);
            sort($expected);

            $response = $this->actingAs($this->supervisor)
                ->post(route('supervisor.ahp.alternatif'), [
                    'selected_produk_ids' => $produkIds,
                ]);

            if (count($expected) < 2) {
                // Fewer than 2 distinct suppliers -> rejected, no session set.
                $response->assertSessionHas('error');
                $this->assertNull(session('ahp_selected_suppliers'));
            } else {
                $response->assertRedirect(route('supervisor.ahp.kriteria'));
                $stored = session('ahp_selected_suppliers');
                sort($stored);
                $this->assertSame($expected, $stored);
            }

            session()->forget('ahp_selected_suppliers');
        });
    }

    /**
     * Example: selecting fewer than 2 products is rejected.
     * Validates: Requirement 10.4
     */
    public function testFewerThanTwoProductsIsRejected(): void
    {
        $supplier = Supplier::create(['kode' => 'SUP-X', 'nama' => 'X']);
        $produk = Produk::create([
            'kode' => 'PRD-X', 'nama' => 'PX', 'satuan' => 'pcs', 'harga' => 1, 'supplier_id' => $supplier->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.ahp.alternatif'), [
                'selected_produk_ids' => [$produk->id],
            ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('ahp_selected_suppliers'));
    }

    /**
     * Example: the alternative selection form lists products and is reachable.
     * Validates: Requirement 10.1
     */
    public function testAlternativeFormListsProducts(): void
    {
        $supplier = Supplier::create(['kode' => 'SUP-LIST', 'nama' => 'Supplier List']);
        Produk::create([
            'kode' => 'PRD-LIST', 'nama' => 'Kertas A4', 'satuan' => 'rim', 'harga' => 1, 'supplier_id' => $supplier->id,
        ]);

        $response = $this->actingAs($this->supervisor)->get(route('supervisor.ahp.alternatif'));

        $response->assertOk();
        $response->assertSee('Kertas A4');
    }
}
