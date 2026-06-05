<?php

namespace App\Http\Controllers\Logistik;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logistik\PenerimaanRequest;
use App\Models\PengadaanHeader;
use App\Models\PengadaanDetail;
use App\Services\Performance\PerformanceCalculator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenerimaanController extends Controller
{
    public function __construct(private PerformanceCalculator $calc) {}

    /**
     * Display the list of Purchase Orders split into pending and completed.
     *
     * Pending  = headers that have at least one detail where jumlah_diterima_baik IS NULL.
     * Completed = headers where all details have been received (none are NULL).
     *
     * Validates: Requirements 4.1, 14.3
     */
    public function index()
    {
        $allHeaders = PengadaanHeader::with(['supplier', 'detail'])
            ->orderBy('id', 'desc')
            ->get();

        $pendingHeaders   = $allHeaders->filter(function (PengadaanHeader $header) {
            return $header->detail->contains(fn (PengadaanDetail $d) => is_null($d->jumlah_diterima_baik));
        })->values();

        $completedHeaders = $allHeaders->filter(function (PengadaanHeader $header) {
            return $header->detail->isNotEmpty()
                && $header->detail->every(fn (PengadaanDetail $d) => !is_null($d->jumlah_diterima_baik));
        })->values();

        return view('logistik.penerimaan.index', compact('pendingHeaders', 'completedHeaders'));
    }

    /**
     * Show the edit form for a specific Purchase Order.
     *
     * Validates: Requirements 4.1
     */
    public function edit(PengadaanHeader $penerimaan)
    {
        $penerimaan->load(['supplier', 'detail.produk']);

        return view('logistik.penerimaan.edit', compact('penerimaan'));
    }

    /**
     * Persist the per-line receiving data, compute per-item metrics.
     *
     * Business rules enforced here (require DB values not available in FormRequest):
     *   Req 4.4: jumlah_diterima_baik <= jumlah_dipesan
     *   Req 4.5: tanggal_kedatangan_aktual >= tanggal_po
     *
     * Validates: Requirements 4.2, 4.4, 4.5, 4.6, 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3, 6.4
     */
    public function update(PenerimaanRequest $request, PengadaanHeader $penerimaan)
    {
        $penerimaan->load('detail');

        // Validate all cross-field business rules before entering the transaction
        foreach ($request->items as $detailId => $item) {
            // Req 4.1 — detail must belong to this header
            $detail = $penerimaan->detail->firstWhere('id', (int) $detailId);

            if (!$detail) {
                abort(404);
            }

            $diterima = (float) $item['jumlah_diterima_baik'];
            $aktual   = $item['tanggal_kedatangan_aktual'];

            // Req 4.4 — received quantity must not exceed ordered quantity
            if ($diterima > (float) $detail->jumlah_dipesan) {
                return back()
                    ->withInput()
                    ->with('error', 'Jumlah diterima tidak boleh melebihi jumlah dipesan.');
            }

            // Req 4.5 — actual arrival date must not be before the PO date
            if (Carbon::parse($aktual)->lt(Carbon::parse($penerimaan->tanggal_po))) {
                return back()
                    ->withInput()
                    ->with('error', 'Tanggal kedatangan aktual tidak boleh sebelum tanggal PO.');
            }
        }

        // All validations passed — persist inside a transaction (Req 4.6)
        DB::transaction(function () use ($request, $penerimaan) {
            foreach ($request->items as $detailId => $item) {
                $detail   = $penerimaan->detail->firstWhere('id', (int) $detailId);
                $diterima = (float) $item['jumlah_diterima_baik'];
                $aktual   = $item['tanggal_kedatangan_aktual'];

                // Req 6.1, 6.2 — days late (clamped at 0)
                $hariKeterlambatan = $this->calc->hariKeterlambatan(
                    $penerimaan->tanggal_kedatangan_target,
                    Carbon::parse($aktual)
                );

                // Req 5.1 — per-item quality percentage
                $persenKualitas = $this->calc->persenKualitasItem(
                    $diterima,
                    (float) $detail->jumlah_dipesan
                );

                // Req 4.2, 5.4 — persist receiving values and computed metrics
                $detail->update([
                    'jumlah_diterima_baik'      => $diterima,
                    'tanggal_kedatangan_aktual' => $aktual,
                    'persen_kualitas_item'       => $persenKualitas,
                    'hari_keterlambatan'         => $hariKeterlambatan,
                ]);
            }
        });

        return redirect()->route('logistik.penerimaan.index')
            ->with('success', 'Data penerimaan berhasil disimpan.');
    }
}
