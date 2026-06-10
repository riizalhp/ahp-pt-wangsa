<?php

namespace Tests\Feature;

use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for role-based authorization and menu visibility.
 *
 * Feature: procurement-supplier-management
 * Validates: Requirements 14.1, 14.2, 14.3, 14.4, 14.5, 14.6
 */
class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $role, string $username): Akun
    {
        return Akun::firstOrCreate(
            ['username' => $username],
            [
                'password_hash' => bcrypt('secret'),
                'nama'          => ucfirst($role),
                'role'          => $role,
            ]
        );
    }

    /**
     * Req 14.6: unauthenticated access to a protected route redirects to login.
     */
    public function testUnauthenticatedAccessRedirectsToLogin(): void
    {
        $this->get(route('supervisor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('sales.dashboard'))->assertRedirect(route('login'));
        $this->get(route('logistik.penerimaan.index'))->assertRedirect(route('login'));
    }

    /**
     * Req 14.4, 14.5: Logistik is denied the supervisor Hasil Penilaian page (403).
     */
    public function testLogistikIsDeniedHasilPenilaian(): void
    {
        $logistik = $this->akun('logistik', 'log1');

        $this->actingAs($logistik)
            ->get(route('supervisor.laporan.penilaian'))
            ->assertForbidden();
    }

    /**
     * Req 14.5: a role accessing another role's route group gets 403.
     */
    public function testCrossRoleAccessIsForbidden(): void
    {
        $sales = $this->akun('sales', 'sales1');
        $this->actingAs($sales)->get(route('supervisor.dashboard'))->assertForbidden();
        $this->actingAs($sales)->get(route('logistik.penerimaan.index'))->assertForbidden();

        $logistik = $this->akun('logistik', 'log1');
        $this->actingAs($logistik)->get(route('sales.dashboard'))->assertForbidden();
    }

    /**
     * Req 14.1: Supervisor can reach its own management pages.
     */
    public function testSupervisorCanAccessOwnPages(): void
    {
        $sup = $this->akun('supervisor', 'sup1');

        $this->actingAs($sup)->get(route('supervisor.dashboard'))->assertOk();
        $this->actingAs($sup)->get(route('supervisor.supplier.index'))->assertOk();
        $this->actingAs($sup)->get(route('supervisor.produk.index'))->assertOk();
        $this->actingAs($sup)->get(route('supervisor.laporan.kinerja'))->assertOk();
    }

    /**
     * Req 14.2: Sales can reach the Purchase Order page.
     */
    public function testSalesCanAccessPurchaseOrder(): void
    {
        $sales = $this->akun('sales', 'sales1');

        $this->actingAs($sales)->get(route('sales.purchase_order.index'))->assertOk();
        $this->actingAs($sales)->get(route('sales.purchase_order.create'))->assertOk();
    }

    /**
     * Req 14.3: Logistik can reach the Penerimaan page.
     */
    public function testLogistikCanAccessPenerimaan(): void
    {
        $logistik = $this->akun('logistik', 'log1');

        $this->actingAs($logistik)->get(route('logistik.penerimaan.index'))->assertOk();
    }
}
