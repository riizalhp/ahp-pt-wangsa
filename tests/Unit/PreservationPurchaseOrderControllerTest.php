<?php

namespace Tests\Unit;

use Tests\PropertyTestCase;
use Eris\Generator;

/**
 * Preservation Property Tests for PurchaseOrderController Business Logic
 *
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7 (Unchanged Behavior)**
 *
 * IMPORTANT: These tests follow observation-first methodology.
 * They capture the CURRENT behavior of non-namespace-related functionality
 * and ensure it remains unchanged after the namespace fix.
 *
 * EXPECTED OUTCOME on UNFIXED code: Tests PASS (confirms baseline behavior)
 * EXPECTED OUTCOME on FIXED code: Tests PASS (confirms no regressions)
 *
 * These tests verify that the following business logic is preserved:
 * - CRUD method signatures remain unchanged
 * - Edit/delete restrictions still check jumlah_diterima_baik
 * - Photo handling still uses 'purchase_orders' storage path
 * - DB::transaction wrappers remain in place
 * - Eager loading still uses with() relationships
 * - Flash message keys and structure remain consistent
 */
class PreservationPurchaseOrderControllerTest extends PropertyTestCase
{
    private string $controllerPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controllerPath = app_path('Http/Controllers/AdminPurchasing/PurchaseOrderController.php');
    }

    /**
     * Property 2.1: CRUD Method Signatures Preservation
     *
     * **Validates: Requirement 3.1**
     *
     * Ensures that CRUD method signatures remain unchanged:
     * - index(): no parameters
     * - create(): no parameters
     * - store(PurchaseOrderRequest $request): takes form request
     * - show(PengadaanHeader $purchase_order): takes model binding
     * - edit(PengadaanHeader $purchase_order): takes model binding
     * - update(PurchaseOrderRequest $request, PengadaanHeader $purchase_order): takes both
     * - destroy(PengadaanHeader $purchase_order): takes model binding
     */
    public function testCrudMethodSignaturesRemainUnchanged(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Test index() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+index\s*\(\s*\)/',
                $content,
                'index() method signature must remain unchanged'
            );

            // Test create() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+create\s*\(\s*\)/',
                $content,
                'create() method signature must remain unchanged'
            );

            // Test store() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+store\s*\(\s*PurchaseOrderRequest\s+\$request\s*\)/',
                $content,
                'store() method signature must remain unchanged'
            );

            // Test show() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+show\s*\(\s*PengadaanHeader\s+\$purchase_order\s*\)/',
                $content,
                'show() method signature must remain unchanged'
            );

            // Test edit() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+edit\s*\(\s*PengadaanHeader\s+\$purchase_order\s*\)/',
                $content,
                'edit() method signature must remain unchanged'
            );

            // Test update() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+update\s*\(\s*PurchaseOrderRequest\s+\$request\s*,\s*PengadaanHeader\s+\$purchase_order\s*\)/',
                $content,
                'update() method signature must remain unchanged'
            );

            // Test destroy() method signature
            $this->assertMatchesRegularExpression(
                '/public\s+function\s+destroy\s*\(\s*PengadaanHeader\s+\$purchase_order\s*\)/',
                $content,
                'destroy() method signature must remain unchanged'
            );
        });
    }

    /**
     * Property 2.2: Edit/Delete Restriction Logic Preservation
     *
     * **Validates: Requirement 3.2**
     *
     * Ensures that edit and delete methods still check jumlah_diterima_baik
     * to prevent modification when items have been received.
     */
    public function testEditDeleteRestrictionLogicPreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Check that edit() method has restriction logic
            $this->assertMatchesRegularExpression(
                '/\$hasReceivedItems\s*=\s*\$purchase_order\s*->\s*detail\s*\(\s*\)\s*->\s*whereNotNull\s*\(\s*[\'"]jumlah_diterima_baik[\'"]\s*\)\s*->\s*exists\s*\(\s*\)/',
                $content,
                'edit() method must preserve restriction check for jumlah_diterima_baik'
            );

            // Check that update() method has restriction logic
            $this->assertMatchesRegularExpression(
                '/\$hasReceivedItems\s*=\s*\$purchase_order\s*->\s*detail\s*\(\s*\)\s*->\s*whereNotNull\s*\(\s*[\'"]jumlah_diterima_baik[\'"]\s*\)\s*->\s*exists\s*\(\s*\)/',
                $content,
                'update() method must preserve restriction check for jumlah_diterima_baik'
            );

            // Check that destroy() method has restriction logic
            $this->assertMatchesRegularExpression(
                '/\$hasReceivedItems\s*=\s*\$purchase_order\s*->\s*detail\s*\(\s*\)\s*->\s*whereNotNull\s*\(\s*[\'"]jumlah_diterima_baik[\'"]\s*\)\s*->\s*exists\s*\(\s*\)/',
                $content,
                'destroy() method must preserve restriction check for jumlah_diterima_baik'
            );

            // Verify that if restriction is triggered, methods redirect with error
            $this->assertMatchesRegularExpression(
                '/if\s*\(\s*\$hasReceivedItems\s*\)\s*\{[\s\S]*?return\s+redirect\(\)[\s\S]*?->with\s*\(\s*[\'"]error[\'"]/m',
                $content,
                'Methods must redirect with error message when items have been received'
            );
        });
    }

    /**
     * Property 2.3: Photo Handling Storage Path Preservation
     *
     * **Validates: Requirement 3.3**
     *
     * Ensures that photo handling methods still use 'purchase_orders' storage path
     * and properly manage photo deletion on update/delete.
     */
    public function testPhotoHandlingStoragePathPreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Check that store() uses 'purchase_orders' path
            $this->assertMatchesRegularExpression(
                '/\$request\s*->\s*file\s*\(\s*[\'"]foto[\'"]\s*\)\s*->\s*store\s*\(\s*[\'"]purchase_orders[\'"]\s*,\s*[\'"]public[\'"]\s*\)/',
                $content,
                'store() method must use "purchase_orders" storage path'
            );

            // Check that update() uses 'purchase_orders' path
            $this->assertMatchesRegularExpression(
                '/\$request\s*->\s*file\s*\(\s*[\'"]foto[\'"]\s*\)\s*->\s*store\s*\(\s*[\'"]purchase_orders[\'"]\s*,\s*[\'"]public[\'"]\s*\)/',
                $content,
                'update() method must use "purchase_orders" storage path'
            );

            // Check that update() deletes old photo before uploading new one
            $this->assertMatchesRegularExpression(
                '/\\\\Storage\s*::\s*disk\s*\(\s*[\'"]public[\'"]\s*\)\s*->\s*delete\s*\(\s*\$purchase_order\s*->\s*foto\s*\)/',
                $content,
                'update() method must delete old photo before uploading new one'
            );

            // Check that destroy() deletes photo
            $this->assertMatchesRegularExpression(
                '/\\\\Storage\s*::\s*disk\s*\(\s*[\'"]public[\'"]\s*\)\s*->\s*delete\s*\(\s*\$purchase_order\s*->\s*foto\s*\)/',
                $content,
                'destroy() method must delete photo'
            );
        });
    }

    /**
     * Property 2.4: Database Transaction Wrappers Preservation
     *
     * **Validates: Requirement 3.4**
     *
     * Ensures that DB::transaction wrappers remain in place for multi-table operations
     * in store(), update(), and destroy() methods.
     */
    public function testDatabaseTransactionWrappersPreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Check that store() uses DB::transaction
            $this->assertMatchesRegularExpression(
                '/DB\s*::\s*transaction\s*\(\s*function\s*\(\s*\)\s*use\s*\(\s*\$request\s*\)\s*\{[\s\S]*?PengadaanHeader\s*::\s*create[\s\S]*?PengadaanDetail\s*::\s*create[\s\S]*?\}\s*\)\s*;/m',
                $content,
                'store() method must wrap operations in DB::transaction'
            );

            // Check that update() uses DB::transaction
            $this->assertMatchesRegularExpression(
                '/DB\s*::\s*transaction\s*\(\s*function\s*\(\s*\)\s*use\s*\(\s*\$request\s*,\s*\$purchase_order\s*\)\s*\{[\s\S]*?\$purchase_order\s*->\s*update[\s\S]*?PengadaanDetail\s*::\s*create[\s\S]*?\}\s*\)\s*;/m',
                $content,
                'update() method must wrap operations in DB::transaction'
            );

            // Check that destroy() uses DB::transaction
            $this->assertMatchesRegularExpression(
                '/DB\s*::\s*transaction\s*\(\s*function\s*\(\s*\)\s*use\s*\(\s*\$purchase_order\s*\)\s*\{[\s\S]*?\$purchase_order\s*->\s*delete[\s\S]*?\}\s*\)\s*;/m',
                $content,
                'destroy() method must wrap operations in DB::transaction'
            );
        });
    }

    /**
     * Property 2.5: Eager Loading Relationships Preservation
     *
     * **Validates: Requirement 3.5**
     *
     * Ensures that eager loading of relationships (supplier, detail.produk)
     * still uses with() method with the same relationship names.
     */
    public function testEagerLoadingRelationshipsPreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Check that index() eager loads 'supplier' and 'detail.produk'
            $this->assertMatchesRegularExpression(
                '/PengadaanHeader\s*::\s*with\s*\(\s*\[\s*[\'"]supplier[\'"]\s*,\s*[\'"]detail\.produk[\'"]\s*\]\s*\)/',
                $content,
                'index() method must eager load supplier and detail.produk relationships'
            );

            // Check that show() loads 'detail.produk'
            $this->assertMatchesRegularExpression(
                '/\$purchase_order\s*->\s*load\s*\(\s*[\'"]detail\.produk[\'"]\s*\)/',
                $content,
                'show() method must load detail.produk relationship'
            );

            // Check that edit() loads 'detail.produk'
            $this->assertMatchesRegularExpression(
                '/\$purchase_order\s*->\s*load\s*\(\s*[\'"]detail\.produk[\'"]\s*\)/',
                $content,
                'edit() method must load detail.produk relationship'
            );
        });
    }

    /**
     * Property 2.6: PengadaanDetail Deletion and Recreation Preservation
     *
     * **Validates: Requirement 3.6**
     *
     * Ensures that update() method still deletes existing details and recreates them.
     */
    public function testPengadaanDetailDeletionRecreationPreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Check that update() deletes existing details
            $this->assertMatchesRegularExpression(
                '/\$purchase_order\s*->\s*detail\s*\(\s*\)\s*->\s*delete\s*\(\s*\)/',
                $content,
                'update() method must delete existing PengadaanDetail records'
            );

            // Check that update() recreates details in a loop
            $this->assertMatchesRegularExpression(
                '/foreach\s*\(\s*\$request\s*->\s*items\s+as\s+\$item\s*\)\s*\{[\s\S]*?PengadaanDetail\s*::\s*create/m',
                $content,
                'update() method must recreate PengadaanDetail records from request items'
            );
        });
    }

    /**
     * Property 2.7: Flash Message Keys and Structure Preservation
     *
     * **Validates: Requirement 3.7**
     *
     * Ensures that flash messages use the same keys ('success', 'error')
     * and maintain consistent message text.
     */
    public function testFlashMessageKeysStructurePreserved(): void
    {
        $this->forAll(
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $content = file_get_contents($filePath);

            // Test that store() returns with 'success' message
            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]success[\'"]\s*,\s*[\'"]Purchase Order berhasil dibuat\.[\'"]\s*\)/',
                $content,
                'store() method must use "success" flash message key'
            );

            // Test that update() returns with 'success' message
            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]success[\'"]\s*,\s*[\'"]Purchase Order berhasil diperbarui\.[\'"]\s*\)/',
                $content,
                'update() method must use "success" flash message key'
            );

            // Test that destroy() returns with 'success' message
            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]success[\'"]\s*,\s*[\'"]Purchase Order berhasil dihapus\.[\'"]\s*\)/',
                $content,
                'destroy() method must use "success" flash message key'
            );

            // Test that error messages use 'error' key
            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]error[\'"]\s*,\s*[\'"]Purchase Order yang sudah diterima tidak dapat diedit\.[\'"]\s*\)/',
                $content,
                'edit() method must use "error" flash message key for restriction'
            );

            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]error[\'"]\s*,\s*[\'"]Purchase Order yang sudah diterima tidak dapat diedit\.[\'"]\s*\)/',
                $content,
                'update() method must use "error" flash message key for restriction'
            );

            $this->assertMatchesRegularExpression(
                '/->with\s*\(\s*[\'"]error[\'"]\s*,\s*[\'"]Purchase Order yang sudah diterima tidak dapat dihapus\.[\'"]\s*\)/',
                $content,
                'destroy() method must use "error" flash message key for restriction'
            );
        });
    }
}
