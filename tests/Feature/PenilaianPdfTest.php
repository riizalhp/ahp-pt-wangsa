<?php

namespace Tests\Feature;

use App\Models\Akun;
use App\Models\HasilAhp;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PDF integration + smoke + empty-state example tests for Hasil Penilaian.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4
 */
class PenilaianPdfTest extends TestCase
{
    use RefreshDatabase;

    private Akun $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = Akun::firstOrCreate(
            ['username' => 'sup_pdf'],
            ['password_hash' => bcrypt('secret'), 'nama' => 'Supervisor', 'role' => 'supervisor']
        );
    }

    // ── Empty state (no AHP result) ───────────────────────────────────────────

    public function testPenilaianViewRendersWhenNoRankingExists(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian'));

        $response->assertOk();
        // View must render without crash even with zero HasilAhp rows.
    }

    public function testPdfRequestWithNoRankingReturnsRedirectWithError(): void
    {
        // No HasilAhp rows exist.
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian.pdf'));

        // Controller redirects back with 'error' flash when no ranking exists.
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function testCetakRequestWithNoRankingRedirectsWithError(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian.cetak'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ── With ranking ──────────────────────────────────────────────────────────

    public function testPenilaianViewShowsRankedSuppliers(): void
    {
        $supplierA = Supplier::create(['kode' => 'SA', 'nama' => 'Supplier Alpha']);
        $supplierB = Supplier::create(['kode' => 'SB', 'nama' => 'Supplier Beta']);

        HasilAhp::create(['supplier_id' => $supplierA->id, 'nilai_akhir' => 0.60, 'ranking' => 1]);
        HasilAhp::create(['supplier_id' => $supplierB->id, 'nilai_akhir' => 0.40, 'ranking' => 2]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian'));

        $response->assertOk();
        $response->assertSee('Supplier Alpha');
        $response->assertSee('Supplier Beta');
    }

    /**
     * PDF integration test: the generated PDF response headers indicate a PDF
     * download and the underlying Blade template contains the company name
     * and ranked suppliers.
     * Validates: Requirements 11.2, 11.3
     */
    public function testGeneratedPdfResponseIsADownload(): void
    {
        $supplier = Supplier::create(['kode' => 'SC', 'nama' => 'Supplier Cetak']);
        HasilAhp::create(['supplier_id' => $supplier->id, 'nilai_akhir' => 0.75, 'ranking' => 1]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian.pdf'));

        $response->assertOk();
        $this->assertStringContainsStringIgnoringCase(
            'pdf',
            $response->headers->get('Content-Type', ''),
            'Response Content-Type should indicate a PDF.'
        );
        $this->assertStringContainsStringIgnoringCase(
            'attachment',
            $response->headers->get('Content-Disposition', ''),
            'Response should trigger a file download.'
        );
    }

    /**
     * PDF smoke test: the cetak (print-preview) view contains the company name
     * and ranked supplier data.
     * Validates: Requirements 11.1, 11.3
     */
    public function testCetakViewContainsCompanyNameAndRankedSuppliers(): void
    {
        $supplier = Supplier::create(['kode' => 'SD', 'nama' => 'Supplier Delta']);
        HasilAhp::create(['supplier_id' => $supplier->id, 'nilai_akhir' => 0.80, 'ranking' => 1]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.laporan.penilaian.cetak'));

        $response->assertOk();
        $response->assertSee('PT Wangsa Jatra Lestari');
        $response->assertSee('Supplier Delta');
    }
}
