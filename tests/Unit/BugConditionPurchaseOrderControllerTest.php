<?php

namespace Tests\Unit;

use Tests\PropertyTestCase;
use Eris\Generator;

/**
 * Bug Condition Exploration Test for PurchaseOrderController Namespace Mismatch
 *
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4 (Current Behavior - Defect)**
 *
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists.
 * DO NOT attempt to fix the test or the code when it fails.
 *
 * This test encodes the EXPECTED behavior (requirements 2.1-2.4).
 * When this test passes after the fix is applied, it confirms the bug is resolved.
 *
 * GOAL: Surface counterexamples that demonstrate the namespace mismatch exists:
 * - Actual namespace: App\Http\Controllers\Sales
 * - Actual Form Request: App\Http\Requests\Sales\PurchaseOrderRequest
 * - Actual view references: sales.purchase_order.*
 * - Actual route redirects: sales.purchase_order.index
 */
class BugConditionPurchaseOrderControllerTest extends PropertyTestCase
{
    private string $controllerPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controllerPath = app_path('Http/Controllers/AdminPurchasing/PurchaseOrderController.php');
    }

    /**
     * Property 1: Bug Condition - Namespace Mismatch Detection
     *
     * **Validates: Requirements 2.1, 2.2, 2.3, 2.4 (Expected Behavior)**
     *
     * This property asserts the EXPECTED behavior:
     * - Namespace should be App\Http\Controllers\AdminPurchasing
     * - Form Request import should be App\Http\Requests\AdminPurchasing\PurchaseOrderRequest
     * - View references should use admin_purchasing.purchase_order.* pattern
     * - Route redirects should use admin_purchasing.purchase_order.index pattern
     *
     * EXPECTED OUTCOME on UNFIXED code: This test FAILS (confirms bug exists)
     * EXPECTED OUTCOME on FIXED code: This test PASSES (confirms bug is fixed)
     */
    public function testNamespaceMismatchDetection(): void
    {
        $this->forAll(
            // Generate a single value (the controller file itself)
            Generator\constant($this->controllerPath)
        )
        ->then(function (string $filePath) {
            $this->assertFileExists($filePath, 'PurchaseOrderController file must exist');

            $content = file_get_contents($filePath);

            // Requirement 2.1: Namespace SHOULD match file location
            $this->assertMatchesRegularExpression(
                '/^namespace\s+App\\\\Http\\\\Controllers\\\\AdminPurchasing\s*;/m',
                $content,
                'Expected namespace App\Http\Controllers\AdminPurchasing but found different namespace. ' .
                'Bug condition: File is in AdminPurchasing directory but declares Sales namespace.'
            );

            // Requirement 2.2: Form Request import SHOULD be AdminPurchasing
            $this->assertMatchesRegularExpression(
                '/use\s+App\\\\Http\\\\Requests\\\\AdminPurchasing\\\\PurchaseOrderRequest\s*;/',
                $content,
                'Expected Form Request import App\Http\Requests\AdminPurchasing\PurchaseOrderRequest. ' .
                'Bug condition: Currently imports from Sales namespace.'
            );

            // Requirement 2.3: View references SHOULD use admin_purchasing.purchase_order.* pattern
            $viewPatterns = [
                'admin_purchasing.purchase_order.index',
                'admin_purchasing.purchase_order.create',
                'admin_purchasing.purchase_order.show',
                'admin_purchasing.purchase_order.edit',
            ];

            foreach ($viewPatterns as $pattern) {
                $escaped = preg_quote($pattern, '/');
                $this->assertMatchesRegularExpression(
                    "/['\"]{$escaped}['\"]/",
                    $content,
                    "Expected view reference '{$pattern}' not found. " .
                    "Bug condition: Currently uses 'sales.purchase_order.*' pattern."
                );
            }

            // Requirement 2.4: Route redirects SHOULD use admin_purchasing.purchase_order.index pattern
            $this->assertMatchesRegularExpression(
                "/route\s*\(\s*['\"]admin_purchasing\.purchase_order\.index['\"]\s*\)/",
                $content,
                "Expected route redirect 'admin_purchasing.purchase_order.index' not found. " .
                "Bug condition: Currently redirects to 'sales.purchase_order.index'."
            );
        });
    }
}
