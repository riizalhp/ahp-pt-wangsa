<?php

namespace App\Http\Controllers\AdminPurchasing;

use App\Http\Controllers\Controller;
use App\Models\HasilAhp;
use App\Models\Pengadaan;
use App\Models\Supplier;
use App\Models\Kriteria;
use App\Models\PenilaianKriteria;
use App\Models\Subkriteria;
use App\Models\PenilaianSubkriteria;
use App\Services\Ahp\AhpCalculatorService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    protected $calculator;

    public function __construct(AhpCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    public function penilaian()
    {
        $rankings = HasilAhp::with('supplier')->orderBy('ranking', 'asc')->get();
        $kriterias = Kriteria::with('subkriteria')->orderBy('id', 'asc')->get();
        $kriteriaIds = $kriterias->pluck('id')->toArray();
        
        $kExistingIndexed = [];
        foreach (PenilaianKriteria::all() as $pk) {
            $kExistingIndexed["{$pk->a_id}-{$pk->b_id}"] = $pk->nilai;
        }

        $kriteriaWeights = [];
        if (count($kExistingIndexed) > 0) {
            $matrix = $this->buildMatrixFromPairs($kriteriaIds, $kExistingIndexed);
            $result = $this->calculator->calculate($matrix);
            foreach ($kriteriaIds as $idx => $id) {
                $kriteriaWeights[$id] = $result->weights[$idx] ?? 0.0;
            }
        }

        // Calculate subkriteria weights (both local and global)
        $subkriterias = Subkriteria::with('kriteria')->orderBy('kriteria_id')->orderBy('id')->get();
        $subkriteriaWeights = []; // Local weights
        $globalSubkriteriaWeights = []; // Global weights
        
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

            // Calculate global weights
            $kW = $kriteriaWeights[$kriteria->id] ?? 0.0;
            foreach ($kSubIds as $id) {
                $globalSubkriteriaWeights[$id] = $kW * ($subkriteriaWeights[$id] ?? 0.0);
            }
        }

        return view('laporan.penilaian', compact(
            'rankings', 
            'kriterias', 
            'kriteriaWeights', 
            'subkriterias', 
            'globalSubkriteriaWeights'
        ));
    }

    protected function buildSubkriteriaMatrix(int $kriteriaId, array $ids): array
    {
        $n = count($ids);
        $matrix = [];

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

    public function pengadaan(Request $request)
    {
        $query = Pengadaan::with(['supplier', 'produk']);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('tanggal_po', [$request->from, $request->to]);
        }

        $pengadaans = $query->orderBy('tanggal_po', 'desc')->get();

        return view('laporan.pengadaan', compact('pengadaans'));
    }

    public function profil()
    {
        $suppliers = Supplier::all();
        return view('laporan.profil', compact('suppliers'));
    }

    public function profilDetail($id)
    {
        $supplier = Supplier::findOrFail($id);
        $pengadaans = Pengadaan::with('produk')
            ->where('supplier_id', $id)
            ->orderBy('tanggal_po', 'desc')
            ->get();

        return view('laporan.profil_detail', compact('supplier', 'pengadaans'));
    }

    protected function buildMatrixFromPairs(array $ids, array $indexedPairs): array
    {
        $n = count($ids);
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $matrix[$i][$j] = 1.0;
                } else {
                    $aId = $ids[$i];
                    $bId = $ids[$j];
                    if (isset($indexedPairs["{$aId}-{$bId}"])) {
                        $matrix[$i][$j] = $indexedPairs["{$aId}-{$bId}"];
                    } elseif (isset($indexedPairs["{$bId}-{$aId}"]) && $indexedPairs["{$bId}-{$aId}"] > 0) {
                        $matrix[$i][$j] = 1.0 / $indexedPairs["{$bId}-{$aId}"];
                    } else {
                        $matrix[$i][$j] = 1.0;
                    }
                }
            }
        }
        return $matrix;
    }
}
