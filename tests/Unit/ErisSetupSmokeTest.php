<?php

namespace Tests\Unit;

use Eris\Generators;
use Tests\PropertyTestCase;

/**
 * Temporary smoke test verifying the giorgiosironi/eris PBT wiring and the
 * minimum-100-iteration guarantee provided by PropertyTestCase.
 */
class ErisSetupSmokeTest extends PropertyTestCase
{
    public function testAdditionIsCommutativeAcrossManyInputs(): void
    {
        $count = 0;

        $this->forAll(
            Generators::int(),
            Generators::int()
        )->then(function (int $a, int $b) use (&$count) {
            $count++;
            $this->assertSame($a + $b, $b + $a);
        });

        // Confirm the property ran the required minimum number of iterations.
        $this->assertGreaterThanOrEqual(
            PropertyTestCase::MIN_ITERATIONS,
            $count,
            'Property should run at least 100 generated iterations.'
        );
    }
}
