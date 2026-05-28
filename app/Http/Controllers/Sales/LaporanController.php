<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\HasilAhp;
use App\Models\Pengadaan;
use App\Models\Supplier;
use App\Models\Kriteria;
use App\Models\PenilaianKriteria;
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
        $kriterias = Kriteria::orderBy('id', 'asc')->get();
        $kriteriaIds = $kriterias->pluck('id')->toArray();
        
        $kExistingIndexed = [];
        foreach (PenilaianKriteria::all() as $pk) {
            $kExistingIndexed["{$pk->a_id}-{$pk->b_id}"] = $pk->nilai;
        }

        $kriteriaWeights = [];
        if (count($kExistingIndexed) > 0) {
            $matrix = $this->buildMatrixFromPairs($kriteriaIds, $kExistingIndexed);
            $result = $this->calculator->calculate($matrix);
            $kriteriaWeights = $result->weights;
        } else {
            $kriteriaWeights = array_fill(0, count($kriterias), 0.0);
        }

        return view('laporan.penilaian', compact('rankings', 'kriterias', 'kriteriaWeights'));
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
