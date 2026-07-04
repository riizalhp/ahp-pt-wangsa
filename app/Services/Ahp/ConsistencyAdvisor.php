<?php

namespace App\Services\Ahp;

class ConsistencyAdvisor
{
    /**
     * Analyze inconsistency and provide recommendations.
     *
     * @param array $matrix Original comparison matrix
     * @param array $weights Calculated eigenvector weights
     * @param array $items Item names/labels (e.g., kriteria names, supplier names)
     * @return array Suggestions with priority levels
     */
    public function analyze(array $matrix, array $weights, array $items): array
    {
        $suggestions = [];
        $n = count($matrix);
        
        if ($n <= 1) {
            return $suggestions;
        }

        // Calculate ideal matrix from weights
        $idealMatrix = $this->calculateIdealMatrix($weights);
        
        // Compare user input with ideal values
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $userValue = $matrix[$i][$j];
                $idealValue = $idealMatrix[$i][$j];
                $gap = abs($userValue - $idealValue);
                
                // Calculate percentage difference
                $percentDiff = $idealValue > 0 ? ($gap / $idealValue) * 100 : 0;
                
                // Only suggest if gap is significant
                if ($gap > 1.0 || $percentDiff > 30) {
                    $priority = $this->determinePriority($gap, $percentDiff);
                    
                    // Round suggested value to nearest valid AHP scale
                    $suggested = $this->roundToAhpScale($idealValue);
                    
                    $suggestions[] = [
                        'pair' => [
                            'i' => $i,
                            'j' => $j,
                            'name_i' => $items[$i] ?? "Item $i",
                            'name_j' => $items[$j] ?? "Item $j",
                        ],
                        'current' => round($userValue, 2),
                        'suggested' => $suggested,
                        'ideal' => round($idealValue, 2),
                        'gap' => round($gap, 2),
                        'percent_diff' => round($percentDiff, 1),
                        'priority' => $priority,
                        'explanation' => $this->generateExplanation($userValue, $idealValue, $items[$i] ?? "Item $i", $items[$j] ?? "Item $j"),
                    ];
                }
            }
        }
        
        // Sort by priority (high first) and then by gap
        usort($suggestions, function ($a, $b) {
            $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            $priorityCompare = $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
            
            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }
            
            return $b['gap'] <=> $a['gap'];
        });
        
        // Limit to top 5 suggestions to avoid overwhelming user
        return array_slice($suggestions, 0, 5);
    }

    /**
     * Calculate ideal matrix from weights (wi/wj).
     *
     * @param array $weights
     * @return array
     */
    protected function calculateIdealMatrix(array $weights): array
    {
        $n = count($weights);
        $idealMatrix = [];
        
        for ($i = 0; $i < $n; $i++) {
            $idealMatrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $idealMatrix[$i][$j] = 1.0;
                } elseif ($weights[$j] > 0) {
                    $idealMatrix[$i][$j] = $weights[$i] / $weights[$j];
                } else {
                    $idealMatrix[$i][$j] = 1.0;
                }
            }
        }
        
        return $idealMatrix;
    }

    /**
     * Determine priority level based on gap and percentage difference.
     *
     * @param float $gap
     * @param float $percentDiff
     * @return string 'high', 'medium', or 'low'
     */
    protected function determinePriority(float $gap, float $percentDiff): string
    {
        // High priority: large absolute gap or large percentage difference
        if ($gap > 2.5 || $percentDiff > 60) {
            return 'high';
        }
        
        // Medium priority: moderate gap or difference
        if ($gap > 1.5 || $percentDiff > 40) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Round value to nearest valid AHP scale (1-9 and their reciprocals).
     *
     * @param float $value
     * @return float
     */
    protected function roundToAhpScale(float $value): float
    {
        // AHP scale: 1, 2, 3, 4, 5, 6, 7, 8, 9
        // For values < 1, we return reciprocal representation
        
        if ($value >= 1) {
            // Round to nearest integer in range 1-9
            $rounded = round($value);
            return max(1, min(9, $rounded));
        } else {
            // For values < 1, find the reciprocal
            if ($value > 0) {
                $reciprocal = 1.0 / $value;
                $rounded = round($reciprocal);
                $rounded = max(1, min(9, $rounded));
                return 1.0 / $rounded;
            }
            return 1.0;
        }
    }

    /**
     * Generate human-readable explanation.
     *
     * @param float $userValue
     * @param float $idealValue
     * @param string $nameI
     * @param string $nameJ
     * @return string
     */
    protected function generateExplanation(float $userValue, float $idealValue, string $nameI, string $nameJ): string
    {
        if ($userValue > $idealValue) {
            $diff = $userValue - $idealValue;
            return "Nilai terlalu besar. Penilaian '{$nameI} vs {$nameJ}' tidak sesuai dengan perbandingan lainnya. Kurangi sekitar " . round($diff, 1) . " poin.";
        } else {
            $diff = $idealValue - $userValue;
            return "Nilai terlalu kecil. Penilaian '{$nameI} vs {$nameJ}' tidak sesuai dengan perbandingan lainnya. Tambahkan sekitar " . round($diff, 1) . " poin.";
        }
    }

    /**
     * Estimate CR if a suggestion is applied.
     * This is a rough estimate - actual CR requires full recalculation.
     *
     * @param array $matrix
     * @param array $suggestion
     * @param AhpCalculatorService $calculator
     * @return float Estimated new CR
     */
    public function estimateCrAfterChange(array $matrix, array $suggestion, AhpCalculatorService $calculator): float
    {
        // Clone matrix
        $testMatrix = $matrix;
        
        // Apply suggestion
        $i = $suggestion['pair']['i'];
        $j = $suggestion['pair']['j'];
        $testMatrix[$i][$j] = $suggestion['suggested'];
        $testMatrix[$j][$i] = 1.0 / $suggestion['suggested'];
        
        // Calculate new CR
        $result = $calculator->calculate($testMatrix);
        
        return $result->CR;
    }

    /**
     * Find most inconsistent pair (highest contribution to inconsistency).
     * This identifies which single pair change would have most impact.
     *
     * @param array $matrix
     * @param array $weights
     * @return array|null [i, j, score]
     */
    public function findMostInconsistentPair(array $matrix, array $weights): ?array
    {
        $n = count($matrix);
        $maxScore = 0;
        $worstPair = null;
        
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $userValue = $matrix[$i][$j];
                $idealValue = $weights[$j] > 0 ? $weights[$i] / $weights[$j] : 1.0;
                
                // Inconsistency score (squared difference for emphasis)
                $score = pow($userValue - $idealValue, 2);
                
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $worstPair = ['i' => $i, 'j' => $j, 'score' => $score];
                }
            }
        }
        
        return $worstPair;
    }
}
