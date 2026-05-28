<?php

namespace App\Services\Ahp;

use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\Supplier;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianSubkriteria;
use App\Models\PenilaianSupplier;
use App\Models\HasilAhp;
use Illuminate\Support\Facades\DB;

class RankingService
{
    protected $calculator;

    public function __construct(AhpCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Compute ranking and persist results.
     *
     * @return array Array of HasilAhp records
     */
    public function computeRanking(): array
    {
        return DB::transaction(function () {
            // 1. Get all suppliers, kriteria, and subkriteria
            $suppliers = Supplier::all();
            $kriterias = Kriteria::all();
            $subkriterias = Subkriteria::all();

            $numSuppliers = $suppliers->count();
            $numKriterias = $kriterias->count();

            if ($numSuppliers === 0 || $numKriterias === 0) {
                // Clear existing rankings
                HasilAhp::truncate();
                return [];
            }

            // 2. Compute Kriteria Weights
            $kriteriaIds = $kriterias->pluck('id')->toArray();
            $kriteriaMatrix = $this->buildKriteriaMatrix($kriteriaIds);
            $kriteriaResult = $this->calculator->calculate($kriteriaMatrix);
            $kriteriaWeights = [];
            foreach ($kriteriaIds as $idx => $id) {
                $kriteriaWeights[$id] = $kriteriaResult->weights[$idx] ?? 0.0;
            }

            // 3. Compute Subkriteria Weights
            $subkriteriaWeights = []; // Local weights per kriteria
            $globalSubkriteriaWeights = []; // Global weights: weight(K) * weight(SK)
            
            foreach ($kriterias as $kriteria) {
                $kSub = $subkriterias->where('kriteria_id', $kriteria->id)->values();
                $kSubIds = $kSub->pluck('id')->toArray();
                $nSub = count($kSubIds);

                if ($nSub === 0) {
                    continue;
                } elseif ($nSub === 1) {
                    $subkriteriaWeights[$kSubIds[0]] = 1.0;
                } else {
                    $subMatrix = $this->buildSubkriteriaMatrix($kriteria->id, $kSubIds);
                    $subResult = $this->calculator->calculate($subMatrix);
                    foreach ($kSubIds as $idx => $id) {
                        $subkriteriaWeights[$id] = $subResult->weights[$idx] ?? 0.0;
                    }
                }

                // Calculate global weights for subkriteria
                $kW = $kriteriaWeights[$kriteria->id] ?? 0.0;
                foreach ($kSubIds as $id) {
                    $globalSubkriteriaWeights[$id] = $kW * ($subkriteriaWeights[$id] ?? 0.0);
                }
            }

            // 4. Compute Supplier Weights per Subkriteria
            $supplierIds = $suppliers->pluck('id')->toArray();
            $supplierWeightsPerSub = []; // format: [$subkriteriaId][$supplierId] = weight

            foreach ($subkriterias as $sub) {
                if ($numSuppliers === 1) {
                    $supplierWeightsPerSub[$sub->id][$supplierIds[0]] = 1.0;
                } else {
                    $supplierMatrix = $this->buildSupplierMatrix($sub->id, $supplierIds);
                    $supplierResult = $this->calculator->calculate($supplierMatrix);
                    foreach ($supplierIds as $idx => $id) {
                        $supplierWeightsPerSub[$sub->id][$id] = $supplierResult->weights[$idx] ?? 0.0;
                    }
                }
            }

            // 5. Calculate Final Score (Nilai Akhir) for each Supplier
            $finalScores = [];
            foreach ($suppliers as $supplier) {
                $score = 0.0;
                foreach ($subkriterias as $sub) {
                    $globalSkW = $globalSubkriteriaWeights[$sub->id] ?? 0.0;
                    $supplierW = $supplierWeightsPerSub[$sub->id][$supplier->id] ?? 0.0;
                    $score += $globalSkW * $supplierW;
                }
                $finalScores[$supplier->id] = $score;
            }

            // 6. Sort Suppliers by Final Score descending
            arsort($finalScores);

            // 7. Clear old results and insert new ones
            HasilAhp::truncate();

            $rank = 1;
            $results = [];
            foreach ($finalScores as $supplierId => $score) {
                $results[] = HasilAhp::create([
                    'supplier_id' => $supplierId,
                    'nilai_akhir' => $score,
                    'ranking' => $rank++,
                ]);
            }

            return $results;
        });
    }

    /**
     * Build pairwise matrix for kriteria.
     */
    protected function buildKriteriaMatrix(array $ids): array
    {
        $n = count($ids);
        $matrix = [];
        
        // Fetch all penilaian kriteria records
        $records = PenilaianKriteria::whereIn('a_id', $ids)
            ->whereIn('b_id', $ids)
            ->get();

        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                } else {
                    $aId = $ids[$i];
                    $bId = $ids[$j];
                    
                    // Look for record a_id = $aId, b_id = $bId
                    $record = $records->first(fn($r) => $r->a_id == $aId && $r->b_id == $bId);
                    if ($record) {
                        $matrix[$i][$j] = $record->nilai;
                    } else {
                        // Look for mirror record a_id = $bId, b_id = $aId
                        $mirror = $records->first(fn($r) => $r->a_id == $bId && $r->b_id == $aId);
                        if ($mirror && $mirror->nilai > 0) {
                            $matrix[$i][$j] = 1.0 / $mirror->nilai;
                        } else {
                            $matrix[$i][$j] = 1.0; // Default fallback
                        }
                    }
                }
            }
        }

        return $matrix;
    }

    /**
     * Build pairwise matrix for subkriteria.
     */
    protected function buildSubkriteriaMatrix(int $kriteriaId, array $ids): array
    {
        $n = count($ids);
        $matrix = [];

        // Fetch records
        $records = PenilaianSubkriteria::where('kriteria_id', $kriteriaId)
            ->whereIn('a_id', $ids)
            ->whereIn('b_id', $ids)
            ->get();

        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                } else {
                    $aId = $ids[$i];
                    $bId = $ids[$j];

                    $record = $records->first(fn($r) => $r->a_id == $aId && $r->b_id == $bId);
                    if ($record) {
                        $matrix[$i][$j] = $record->nilai;
                    } else {
                        $mirror = $records->first(fn($r) => $r->a_id == $bId && $r->b_id == $aId);
                        if ($mirror && $mirror->nilai > 0) {
                            $matrix[$i][$j] = 1.0 / $mirror->nilai;
                        } else {
                            $matrix[$i][$j] = 1.0;
                        }
                    }
                }
            }
        }

        return $matrix;
    }

    /**
     * Build pairwise matrix for suppliers.
     */
    protected function buildSupplierMatrix(int $subkriteriaId, array $ids): array
    {
        $n = count($ids);
        $matrix = [];

        // Fetch records
        $records = PenilaianSupplier::where('subkriteria_id', $subkriteriaId)
            ->whereIn('a_supplier_id', $ids)
            ->whereIn('b_supplier_id', $ids)
            ->get();

        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                } else {
                    $aId = $ids[$i];
                    $bId = $ids[$j];

                    $record = $records->first(fn($r) => $r->a_supplier_id == $aId && $r->b_supplier_id == $bId);
                    if ($record) {
                        $matrix[$i][$j] = $record->nilai;
                    } else {
                        $mirror = $records->first(fn($r) => $r->a_supplier_id == $bId && $r->b_supplier_id == $aId);
                        if ($mirror && $mirror->nilai > 0) {
                            $matrix[$i][$j] = 1.0 / $mirror->nilai;
                        } else {
                            $matrix[$i][$j] = 1.0;
                        }
                    }
                }
            }
        }

        return $matrix;
    }
}
