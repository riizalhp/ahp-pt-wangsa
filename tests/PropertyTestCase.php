<?php

namespace Tests;

use Eris\TestTrait;

/**
 * Base test case for property-based tests in the
 * procurement-supplier-management feature.
 *
 * It wires in giorgiosironi/eris via {@see TestTrait} and guarantees that every
 * property runs a minimum of {@see self::MIN_ITERATIONS} generated iterations
 * (the design's Testing Strategy requires at least 100).
 *
 * Usage in a concrete property test:
 *
 *   class PerformanceCalculatorTest extends PropertyTestCase
 *   {
 *       public function testSomeProperty(): void
 *       {
 *           $this->forAll(Generators\nat(), Generators\nat())
 *               ->then(function (int $a, int $b) {
 *                   $this->assertSame($a + $b, $b + $a);
 *               });
 *       }
 *   }
 *
 * Pure-logic property tests should live under tests/Unit and extend this class.
 * Property tests that require a database round-trip should live under
 * tests/Feature, extend this class, and additionally use the RefreshDatabase
 * trait.
 */
abstract class PropertyTestCase extends TestCase
{
    use TestTrait;

    /**
     * Minimum number of generated iterations every property must run.
     */
    public const MIN_ITERATIONS = 100;

    protected function setUp(): void
    {
        parent::setUp();

        // Eris defaults to 100 iterations; enforce the floor explicitly so the
        // minimum holds even if a subclass or attribute lowers it.
        if (! isset($this->iterations) || $this->iterations < self::MIN_ITERATIONS) {
            $this->limitTo(self::MIN_ITERATIONS);
        }
    }
}
