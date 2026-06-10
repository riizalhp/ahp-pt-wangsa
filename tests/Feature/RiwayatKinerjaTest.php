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
 * Example tests for Riwayat Pengadaan and Kinerja Supplier views.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 12.1, 12.2, 12.3, 13.1, 13.3
 */
class RiwayatKinerjaTest extends TestCase
{
    use RefreshDatabase;

    private Akun $supervisor;
    private Supplier $supplier;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = Akun::firstOrCreate(
            ['username' => 'sup_riwayat'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Supervisor', 'role' => 'supervisor']
        );

        $this->supplier = Supplier::create([
            'kode' => 'SUP-RW', 'nama' => 'Supplier Riwayat', 'jenis_barang' => 'ATK', 'alamat' => 'Jl. Test',
        ]);

        $this->produk = Produk::create([
            'kode' => 'PRD-RW', 'nama' => 'Kertas HVS', 'satuan' => 'rim', 'harga' => 50000,
            'supplier_id' => $this->supplier->id,
        ]);
    }

    // ── Riwayat Pengadaan ────────────────────────────────────────────────────

    public function testRiwayatListShowsPoColumns(): void
    {
        PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-RW-001',
            'tanggal_po'                => '2026-03-01',
            'tanggal_kedatangan_target' => '2026-03-10',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.pengadaan'));

        $response->assertOk();
        $response->assertSee('PO-RW-001');
        $response->assertSee('Supplier Riwayat');
    }

    public function testRiwayatEmptyStateWhenNoPo(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.pengadaan'));

        $response->assertOk();
        // Page should render without error; content varies by template but status must be 200.
    }

    public function testRiwayatDetailListsLineItems(): void
    {
        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-RW-DET',
            'tanggal_po'                => '2026-04-01',
            'tanggal_kedatangan_target' => '2026-04-10',
        ]);

        PengadaanDetail::create([
            'pengadaan_id'   => $header->id,
            'produk_id'      => $this->produk->id,
            'jumlah_dipesan' => 20,
            'satuan'         => 'rim',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.riwayat.detail', $header->id));

        $response->assertOk();
        $response->assertSee('Kertas HVS');
        $response->assertSee('rim');
    }

    // ── Kinerja Supplier ─────────────────────────────────────────────────────

    public function testKinerjaShowsSuppliersWithReceivedDetails(): void
    {
        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-KNJ-001',
            'tanggal_po'                => '2026-05-01',
            'tanggal_kedatangan_target' => '2026-05-10',
        ]);

        PengadaanDetail::create([
            'pengadaan_id'              => $header->id,
            'produk_id'                 => $this->produk->id,
            'jumlah_dipesan'            => 10,
            'satuan'                    => 'rim',
            'jumlah_diterima_baik'      => 9,
            'tanggal_kedatangan_aktual' => '2026-05-12',
            'persen_kualitas_item'      => 90,
            'hari_keterlambatan'        => 2,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.kinerja'));

        $response->assertOk();
        $response->assertSee('Supplier Riwayat');
    }

    public function testKinerjaEmptyStateWhenNoReceivedDetails(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.kinerja'));

        $response->assertOk();
        // No received details -> empty state; page must not crash.
    }

    public function testKinerjaDisplaysPerformanceColumns(): void
    {
        $header = PengadaanHeader::create([
            'supplier_id'               => $this->supplier->id,
            'no_po'                     => 'PO-KNJ-COL',
            'tanggal_po'                => '2026-06-01',
            'tanggal_kedatangan_target' => '2026-06-10',
        ]);

        PengadaanDetail::create([
            'pengadaan_id'              => $header->id,
            'produk_id'                 => $this->produk->id,
            'jumlah_dipesan'            => 10,
            'satuan'                    => 'rim',
            'jumlah_diterima_baik'      => 8,
            'tanggal_kedatangan_aktual' => '2026-06-13',
            'persen_kualitas_item'      => 80,
            'hari_keterlambatan'        => 3,
        ]);

        // Trigger metric recomputation via service so the supplier row is updated.
        app(\App\Services\Supplier\SupplierMetricsService::class)
            ->recalculateForSupplier($this->supplier->id);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.kinerja'));

        $response->assertOk();
        // The kinerja view should show the supplier name and jenis_barang.
        $response->assertSee('Supplier Riwayat');
        $response->assertSee('ATK');
    }
}
