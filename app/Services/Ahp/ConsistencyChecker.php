<?php

namespace App\Services\Ahp;

class ConsistencyChecker
{
    /**
     * Check if the AHP result is consistent.
     *
     * @param AhpResult $result
     * @return bool
     */
    public function isConsistent(AhpResult $result): bool
    {
        return $result->consistent;
    }

    /**
     * Lookup Random Index (RI) for a given matrix size.
     *
     * @param int $n
     * @return float
     */
    public function lookupRi(int $n): float
    {
        return AhpCalculatorService::RI[$n] ?? 1.49;
    }
}
