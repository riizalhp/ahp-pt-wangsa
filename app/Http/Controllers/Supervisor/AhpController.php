<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\Subkriteria;
use App\Models\Supplier;
use App\Models\Produk;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianSubkriteria;
use App\Models\PenilaianSupplier;
use App\Models\HasilAhp;
use App\Services\Ahp\AhpCalculatorService;
use App\Services\Ahp\RankingService;
use App\Support\NameSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AhpController extends Controller
{
    protected $calculator;
    protected $rankingService;

    public function __construct(AhpCalculatorService $calculator, RankingService $rankingService)
    {
        $this->calculator = $calculator;
        $this->rankingService = $rankingService;
    }

    /**
     * Step 1: Pairwise Kriteria Form
     */
    public function kriteriaForm()
    {
        $kriterias = Kriteria::orderBy('id', 'asc')->get();
        $n = $kriterias->count();
        
        // Generate pairs
        $pairs = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $pairs[] = [
                    'a' => $kriterias[$i],
                    'b' => $kriterias[$j]
                ];
            }
        }

        // Load existing values
        $existing = PenilaianKriteria::all()->pluck('nilai', 'a_id_b_id')->toArray();
        // We will index existing by "a_id-b_id" for easy lookup
        $existingIndexed = [];
        foreach (PenilaianKriteria::all() as $pk) {
            $existingIndexed["{$pk->a_id}-{$pk->b_id}"] = $pk->nilai;
        }

        // Calculate current CR if all pairs are filled
        $cr = null;
        $isConsistent = true;
        if (count($existingIndexed) >= count($pairs)) {
            $matrix = $this->buildMatrixFromPairs($kriterias->pluck('id')->toArray(), $existingIndexed);
            $result = $this->calculator->calculate($matrix);
            $cr = $result->CR;
            $isConsistent = $result->consistent;
        }

        return view('supervisor.ahp.kriteria', compact('kriterias', 'pairs', 'existingIndexed', 'cr', 'isConsistent'));
    }

    /**
     * Save Step 1: Pairwise Kriteria
     */
    public function kriteriaSave(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
        ]);

        $kriterias = Kriteria::orderBy('id', 'asc')->get();
        $kriteriaIds = $kriterias->pluck('id')->toArray();

        DB::transaction(function () use ($request, $kriteriaIds) {
            PenilaianKriteria::query()->delete();
            foreach ($request->nilai as $aId => $bGroup) {
                foreach ($bGroup as $bId => $val) {
                    if (in_array($aId, $kriteriaIds) && in_array($bId, $kriteriaIds)) {
                        PenilaianKriteria::create([
                            'a_id' => $aId,
                            'b_id' => $bId,
                            'nilai' => floatval($val),
                        ]);
                    }
                }
            }
        });

        // Re-calculate to check consistency
        $existingIndexed = [];
        foreach (PenilaianKriteria::all() as $pk) {
            $existingIndexed["{$pk->a_id}-{$pk->b_id}"] = $pk->nilai;
        }
        $matrix = $this->buildMatrixFromPairs($kriteriaIds, $existingIndexed);
        $result = $this->calculator->calculate($matrix);

        if (!$result->consistent) {
            return redirect()->route('supervisor.ahp.kriteria')
                ->with('error', 'Matriks perbandingan kriteria TIDAK KONSISTEN (CR = ' . round($result->CR, 4) . ' > 0.1). Silakan sesuaikan kembali nilai Anda.');
        }

        return redirect()->route('supervisor.ahp.subkriteria')
            ->with('success', 'Perbandingan kriteria berhasil disimpan dan konsisten (CR = ' . round($result->CR, 4) . '). Silakan lanjutkan ke subkriteria.');
    }

    /**
     * Step 2: Pairwise Subkriteria Form
     */
    public function subkriteriaForm()
    {
        // Load criteria which have > 1 subcriteria
        $kriterias = Kriteria::with(['subkriteria' => function ($query) {
            $query->orderBy('id', 'asc');
        }])->get()->filter(fn($k) => $k->subkriteria->count() > 1);

        $existingIndexed = [];
        foreach (PenilaianSubkriteria::all() as $ps) {
            $existingIndexed["{$ps->kriteria_id}-{$ps->a_id}-{$ps->b_id}"] = $ps->nilai;
        }

        // Calculate CRs for preview
        $kriteriaCrs = [];
        foreach ($kriterias as $kriteria) {
            $subIds = $kriteria->subkriteria->pluck('id')->toArray();
            $nSub = count($subIds);
            $pairsCount = $nSub * ($nSub - 1) / 2;
            
            // Check if we have all pairs filled for this kriteria
            $filledCount = 0;
            $kSubIndexed = [];
            foreach ($existingIndexed as $key => $val) {
                if (str_starts_with($key, "{$kriteria->id}-")) {
                    $filledCount++;
                    // Remove kriteria_id from key
                    $subKey = substr($key, strlen("{$kriteria->id}-"));
                    $kSubIndexed[$subKey] = $val;
                }
            }

            if ($filledCount >= $pairsCount) {
                $matrix = $this->buildMatrixFromPairs($subIds, $kSubIndexed);
                $result = $this->calculator->calculate($matrix);
                $kriteriaCrs[$kriteria->id] = [
                    'cr' => $result->CR,
                    'consistent' => $result->consistent
                ];
            }
        }

        return view('supervisor.ahp.subkriteria', compact('kriterias', 'existingIndexed', 'kriteriaCrs'));
    }

    /**
     * Save Step 2: Pairwise Subkriteria
     */
    public function subkriteriaSave(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            PenilaianSubkriteria::query()->delete();
            foreach ($request->nilai as $kriteriaId => $aGroup) {
                foreach ($aGroup as $aId => $bGroup) {
                    foreach ($bGroup as $bId => $val) {
                        PenilaianSubkriteria::create([
                            'kriteria_id' => $kriteriaId,
                            'a_id' => $aId,
                            'b_id' => $bId,
                            'nilai' => floatval($val),
                        ]);
                    }
                }
            }
        });

        // Verify consistency for all criteria groups
        $kriterias = Kriteria::with('subkriteria')->get()->filter(fn($k) => $k->subkriteria->count() > 1);
        $existingIndexed = [];
        foreach (PenilaianSubkriteria::all() as $ps) {
            $existingIndexed["{$ps->kriteria_id}-{$ps->a_id}-{$ps->b_id}"] = $ps->nilai;
        }

        $inconsistentKriterias = [];
        foreach ($kriterias as $kriteria) {
            $subIds = $kriteria->subkriteria->pluck('id')->toArray();
            $kSubIndexed = [];
            foreach ($existingIndexed as $key => $val) {
                if (str_starts_with($key, "{$kriteria->id}-")) {
                    $subKey = substr($key, strlen("{$kriteria->id}-"));
                    $kSubIndexed[$subKey] = $val;
                }
            }
            $matrix = $this->buildMatrixFromPairs($subIds, $kSubIndexed);
            $result = $this->calculator->calculate($matrix);
            
            if (!$result->consistent) {
                $inconsistentKriterias[] = "{$kriteria->nama} (CR = " . round($result->CR, 4) . ")";
            }
        }

        if (count($inconsistentKriterias) > 0) {
            return redirect()->route('supervisor.ahp.subkriteria')
                ->with('error', 'Ada grup subkriteria yang TIDAK KONSISTEN: ' . implode(', ', $inconsistentKriterias) . '. Silakan sesuaikan kembali nilai Anda.');
        }

        return redirect()->route('supervisor.ahp.supplier')
            ->with('success', 'Perbandingan subkriteria berhasil disimpan dan semua konsisten. Silakan lanjutkan ke perbandingan supplier.');
    }

    /**
     * Step 0: Select Alternative Products for AHP (Req 10.1, 10.3)
     */
    public function alternatifForm(Request $request)
    {
        $search = $request->input('search');
        $query = Produk::with('supplier');
        $query = NameSearch::filter($query, 'nama', $search);
        $produks = $query->get();

        return view('supervisor.ahp.alternatif', compact('produks', 'search'));
    }

    /**
     * Save Step 0: Store selected alternative suppliers in session (Req 10.2, 10.4, 10.5)
     */
    public function alternatifSave(Request $request)
    {
        // Validate at least 2 products selected (Req 10.4)
        if (
            !$request->has('selected_produk_ids') ||
            !is_array($request->input('selected_produk_ids')) ||
            count($request->input('selected_produk_ids')) < 2
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pilih minimal 2 produk untuk perbandingan AHP.');
        }

        $request->validate([
            'selected_produk_ids'   => 'required|array|min:2',
            'selected_produk_ids.*' => 'integer|exists:data_produk,id',
        ]);

        // Load selected products with their supplier (Req 10.2, 10.5)
        $produks = Produk::with('supplier')
            ->whereIn('id', $request->selected_produk_ids)
            ->get();

        // Extract unique supplier IDs from the selected products
        $supplierIds = $produks
            ->pluck('supplier_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Guard: all selected products must have a supplier linked
        $produksTanpaSupplier = $produks->filter(fn($p) => is_null($p->supplier_id));
        if ($produksTanpaSupplier->isNotEmpty()) {
            $names = $produksTanpaSupplier->pluck('nama')->implode(', ');
            return redirect()->back()
                ->withInput()
                ->with('error', "Produk berikut belum memiliki supplier: {$names}. Hubungkan produk ke supplier terlebih dahulu.");
        }

        // Guard: need at least 2 distinct suppliers to run AHP pairwise comparison
        if (count($supplierIds) < 2) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Produk yang dipilih semuanya berasal dari supplier yang sama. Pilih produk dari minimal 2 supplier berbeda untuk perbandingan AHP.');
        }

        // Store in session so supplierForm can filter by these suppliers
        session(['ahp_selected_suppliers' => $supplierIds]);

        return redirect()->route('supervisor.ahp.kriteria')
            ->with('success', 'Alternatif produk berhasil dipilih. Lanjutkan dengan perbandingan kriteria.');
    }

    /**
     * Step 3: Pairwise Supplier Form
     */
    public function supplierForm()
    {
        // Filter to session-selected suppliers if set (Req 10.5)
        if (session()->has('ahp_selected_suppliers')) {
            $suppliers = Supplier::whereIn('id', session('ahp_selected_suppliers'))->get();
        } else {
            $suppliers = Supplier::all();
        }
        $subkriterias = Subkriteria::with('kriteria')->get();

        if ($suppliers->count() < 2) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Minimal harus ada 2 supplier untuk melakukan perbandingan.');
        }

        $existingIndexed = [];
        foreach (PenilaianSupplier::all() as $ps) {
            $existingIndexed["{$ps->subkriteria_id}-{$ps->a_supplier_id}-{$ps->b_supplier_id}"] = $ps->nilai;
        }

        // Calculate CRs for preview
        $subkriteriaCrs = [];
        $supplierIds = $suppliers->pluck('id')->toArray();
        $pairsCount = count($supplierIds) * (count($supplierIds) - 1) / 2;

        foreach ($subkriterias as $sub) {
            $filledCount = 0;
            $subIndexed = [];
            foreach ($existingIndexed as $key => $val) {
                if (str_starts_with($key, "{$sub->id}-")) {
                    $filledCount++;
                    $supKey = substr($key, strlen("{$sub->id}-"));
                    $subIndexed[$supKey] = $val;
                }
            }

            if ($filledCount >= $pairsCount) {
                $matrix = $this->buildMatrixFromPairs($supplierIds, $subIndexed);
                $result = $this->calculator->calculate($matrix);
                $subkriteriaCrs[$sub->id] = [
                    'cr' => $result->CR,
                    'consistent' => $result->consistent
                ];
            }
        }

        return view('supervisor.ahp.supplier', compact('suppliers', 'subkriterias', 'existingIndexed', 'subkriteriaCrs'));
    }

    /**
     * Save Step 3: Pairwise Supplier
     */
    public function supplierSave(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            PenilaianSupplier::query()->delete();
            foreach ($request->nilai as $subkriteriaId => $aGroup) {
                foreach ($aGroup as $aId => $bGroup) {
                    foreach ($bGroup as $bId => $val) {
                        PenilaianSupplier::create([
                            'subkriteria_id' => $subkriteriaId,
                            'a_supplier_id' => $aId,
                            'b_supplier_id' => $bId,
                            'nilai' => floatval($val),
                        ]);
                    }
                }
            }
        });

        // Verify consistency for all subkriteria groups
        $suppliers = Supplier::all();
        $supplierIds = $suppliers->pluck('id')->toArray();
        $subkriterias = Subkriteria::all();
        
        $existingIndexed = [];
        foreach (PenilaianSupplier::all() as $ps) {
            $existingIndexed["{$ps->subkriteria_id}-{$ps->a_supplier_id}-{$ps->b_supplier_id}"] = $ps->nilai;
        }

        $inconsistentSubs = [];
        foreach ($subkriterias as $sub) {
            $subIndexed = [];
            foreach ($existingIndexed as $key => $val) {
                if (str_starts_with($key, "{$sub->id}-")) {
                    $supKey = substr($key, strlen("{$sub->id}-"));
                    $subIndexed[$supKey] = $val;
                }
            }
            $matrix = $this->buildMatrixFromPairs($supplierIds, $subIndexed);
            $result = $this->calculator->calculate($matrix);
            
            if (!$result->consistent) {
                $inconsistentSubs[] = "Subkriteria {$sub->nama} (CR = " . round($result->CR, 4) . ")";
            }
        }

        if (count($inconsistentSubs) > 0) {
            return redirect()->route('supervisor.ahp.supplier')
                ->with('error', 'Ada grup perbandingan supplier yang TIDAK KONSISTEN: ' . implode(', ', $inconsistentSubs) . '. Silakan sesuaikan kembali nilai Anda.');
        }

        // Run full AHP ranking service to calculate and persist final results!
        // Only rank the suppliers whose products were selected as alternatives (Req 10.5)
        $selectedSupplierIds = session('ahp_selected_suppliers');
        $this->rankingService->computeRanking($selectedSupplierIds);

        return redirect()->route('supervisor.ahp.hasil')
            ->with('success', 'Perbandingan supplier berhasil disimpan dan konsisten. Perhitungan ranking AHP selesai!');
    }

    /**
     * Step 4: Hasil AHP & Rankings
     */
    public function hasil()
    {
        $rankings = HasilAhp::with('supplier')
            ->orderBy('ranking', 'asc')
            ->get();

        // Load kriteria weights for the donut chart
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

        return view('supervisor.ahp.hasil', compact('rankings', 'kriterias', 'kriteriaWeights'));
    }

    /**
     * Helper to build matrix from indexed pairs
     */
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
                        $matrix[$i][$j] = 1.0; // fallback
                    }
                }
            }
        }

        return $matrix;
    }
}
