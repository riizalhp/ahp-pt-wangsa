<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\HasilAhp;
use App\Models\Pengadaan;
use App\Models\PengadaanHeader;
use App\Models\Supplier;
use App\Models\Kriteria;
use App\Models\PenilaianKriteria;
use App\Services\Ahp\AhpCalculatorService;
use App\Services\Report\PenilaianPdfService;
use App\Services\Supplier\SupplierMetricsService;
use Illuminate\Http\Request;

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

    public function kinerja()
    {
        $suppliers = $this->metricsService->suppliersWithPerformance();
        return view('laporan.kinerja', compact('suppliers'));
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
