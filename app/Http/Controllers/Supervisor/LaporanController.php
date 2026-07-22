<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\HasilAhp;
use App\Models\Pengadaan;
use App\Models\PengadaanHeader;
use App\Models\Supplier;
use App\Models\Kriteria;
use App\Models\PenilaianKriteria;
use App\Models\Subkriteria;
use App\Models\PenilaianSubkriteria;
use App\Models\PenilaianSupplier;
use App\Services\Ahp\AhpCalculatorService;
use App\Services\Report\PenilaianPdfService;
use App\Services\Supplier\SupplierMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    protected $calculator;
    protected $metricsService;

    public function __construct(AhpCalculatorService $calculator, SupplierMetricsService $metricsService)
    {
        $this->calculator = $calculator;
        $this->metricsService = $metricsService;
    }

    public function penilaianPdf(PenilaianPdfService $pdfService)
    {
        if (!$pdfService->hasRanking()) {
            return redirect()->back()->with('error', 'Belum ada hasil penilaian. Jalankan perhitungan AHP terlebih dahulu.');
        }
        return $pdfService->generatePdf();
    }

    /**
     * Tampilkan versi cetak (print-friendly) yang otomatis memicu
     * dialog print browser (seperti Ctrl + P) sehingga user bisa
     * menyimpan sebagai PDF langsung dari browser.
     */
    public function penilaianCetak(PenilaianPdfService $pdfService)
    {
        if (!$pdfService->hasRanking()) {
            return redirect()
                ->route('supervisor.laporan.penilaian')
                ->with('error', 'Belum ada hasil penilaian. Jalankan perhitungan AHP terlebih dahulu.');
        }

        $rankings = $pdfService->getRankings();
        $companyName = 'PT Wangsa Jatra Lestari';

        // Calculate kriteria weights
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

        return view('laporan.penilaian_cetak', compact(
            'rankings', 
            'companyName', 
            'kriterias', 
            'kriteriaWeights',
            'subkriterias',
            'subkriteriaWeights',
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

    public function kinerja()
    {
        $suppliers = $this->metricsService->suppliersWithPerformance();
        return view('laporan.kinerja', compact('suppliers'));
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

    public function pengadaan()
    {
        $pengadaans = PengadaanHeader::with(['supplier', 'detail.produk'])
            ->orderBy('id', 'desc')
            ->get();

        return view('laporan.pengadaan', compact('pengadaans'));
    }

    public function riwayatDetail($id)
    {
        $header = PengadaanHeader::with(['supplier', 'detail.produk'])->findOrFail($id);

        return view('laporan.riwayat_detail', compact('header'));
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

    public function resetPenilaian()
    {
        DB::transaction(function () {
            // Delete all penilaian data (kriteria, subkriteria, supplier comparisons and results)
            PenilaianKriteria::query()->delete();
            PenilaianSubkriteria::query()->delete();
            PenilaianSupplier::query()->delete();
            HasilAhp::query()->delete();
            
            // Clear session data
            session()->forget('ahp_selected_suppliers');
            \Illuminate\Support\Facades\Cache::forget('ahp_selected_products');
        });

        return redirect()
            ->route('supervisor.laporan.penilaian')
            ->with('success', 'Semua data penilaian berhasil direset. Kriteria, subkriteria, dan supplier sekarang dapat dihapus. Silakan mulai penilaian baru dari awal.');
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
