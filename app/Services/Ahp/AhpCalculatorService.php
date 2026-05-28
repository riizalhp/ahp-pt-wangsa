<?php

namespace App\Services\Ahp;

class AhpCalculatorService
{
    // Random Index Table (Saaty scale, 0-indexed where index 0 is n=1, index 1 is n=2, etc.)
    const RI = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49
    ];

    /**
     * Calculate AHP weights and consistency metrics for a given matrix.
     *
     * @param array $matrix 2D square matrix of size n x n
     * @return AhpResult
     */
    public function calculate(array $matrix): AhpResult
    {
        $n = count($matrix);
        
        if ($n === 0) {
            return new AhpResult([], [], 0.0, 0.0, 0.0, true);
        }

        if ($n === 1) {
            return new AhpResult([[1.0]], [1.0], 1.0, 0.0, 0.0, true);
        }

        // 1. Calculate column sums
        $colSums = array_fill(0, $n, 0.0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $colSums[$j] += $matrix[$i][$j];
            }
        }

        // 2. Normalize the matrix
        $normalized = [];
        for ($i = 0; $i < $n; $i++) {
            $normalized[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $normalized[$i][$j] = $colSums[$j] > 0 ? $matrix[$i][$j] / $colSums[$j] : 0.0;
            }
        }

        // 3. Calculate row averages (weights)
        $weights = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            $rowSum = array_sum($normalized[$i]);
            $weights[$i] = $rowSum / $n;
        }

        // 4. Calculate Lambda Max
        // First compute the weighted sum vector: WSV = Matrix * Weights
        $wsv = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $wsv[$i] += $matrix[$i][$j] * $weights[$j];
            }
        }

        // Calculate lambda values by dividing WSV by weights
        $lambdas = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            $lambdas[$i] = $weights[$i] > 0 ? $wsv[$i] / $weights[$i] : 0.0;
        }

        $lambdaMax = array_sum($lambdas) / $n;

        // 5. Consistency Index (CI)
        $CI = ($lambdaMax - $n) / ($n - 1);

        // 6. Consistency Ratio (CR)
        $ri = self::RI[$n] ?? 1.49; // Default fallback to n=10 if greater
        $CR = $ri > 0 ? $CI / $ri : 0.0;

        // An matrix is consistent if CR <= 0.10. For n <= 2, it is always consistent.
        $consistent = ($n <= 2) || ($CR <= 0.10);

        return new AhpResult($normalized, $weights, $lambdaMax, $CI, $CR, $consistent);
    }
}
