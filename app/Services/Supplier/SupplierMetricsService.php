<?php

namespace App\Services\Supplier;

use App\Models\PengadaanDetail;
use App\Models\Supplier;
use App\Services\Performance\PerformanceCalculator;
use Illuminate\Support\Collection;

/**
 * Aggregates per-supplier procurement performance metrics by delegating the
 * math to PerformanceCalculator and persisting results back to data_supplier.
 *
 * A detail row is considered "received" when both jumlah_diterima_baik and
 * tanggal_kedatangan_aktual are not null.
 *
 * Requirements: 7.1, 8.1, 9.1, 9.2, 9.3, 13.2
 */
class SupplierMetricsService
{
    public function __construct(private PerformanceCalculator $calc) {}

    /**
     * Recompute and persist supplier-level performance aggregates after a
     * receiving event.
     *
     * Loads all received detail rows for the given supplier (via
     * PengadaanHeader → PengadaanDetail), builds the input arrays that the
     * PerformanceCalculator expects, and persists the three aggregates on the
     * data_supplier row.
     *
     * When there are zero received details every aggregate is forced to 0.0 —
     * totalPersenCacatSupplier is NOT called in that case because
     * 100 − 0 = 100 would be semantically wrong for an empty state (Req 9.3).
     *
     * Requirements: 7.1, 8.1, 9.1, 9.2
     *
     * @param int $supplierId  Primary key of the supplier to recalculate.
     */
    public function recalculateForSupplier(int $supplierId): void
    {
        $supplier = Supplier::find($supplierId);

        if (! $supplier) {
            return;
        }

        // Load all received detail rows for this supplier via the header relation.
        // "Received" means jumlah_diterima_baik IS NOT NULL AND tanggal_kedatangan_aktual IS NOT NULL.
        $receivedDetails = PengadaanDetail::whereNotNull('jumlah_diterima_baik')
            ->whereNotNull('tanggal_kedatangan_aktual')
            ->whereHas('header', function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->with('header:id,supplier_id')
            ->get(['id', 'pengadaan_id', 'hari_keterlambatan', 'persen_kualitas_item']);

        if ($receivedDetails->isEmpty()) {
            $supplier->update([
                'total_persen_keterlambatan' => 0.0,
                'mean_hari_keterlambatan'    => 0.0,
                'total_persen_cacat'         => 0.0,
            ]);
            return;
        }

        // Build the input arrays required by PerformanceCalculator.
        $hariKeterlambatanList  = [];
        $receivedItems          = [];
        $persenKualitasItemList = [];

        foreach ($receivedDetails as $detail) {
            $hariKeterlambatanList[]  = (int) $detail->hari_keterlambatan;

            $receivedItems[] = [
                'pengadaan_id'       => (int) $detail->pengadaan_id,
                'hari_keterlambatan' => (int) $detail->hari_keterlambatan,
            ];

            $persenKualitasItemList[] = (float) $detail->persen_kualitas_item;
        }

        // Delegate math to the pure calculator (Requirements 7.1, 8.1, 9.1, 9.2).
        $persenKeterlambatan = $this->calc->persenKeterlambatanSupplier($hariKeterlambatanList);
        $mean                = $this->calc->meanHariKeterlambatan($receivedItems);
        $kumulatifQuality    = $this->calc->persenKualitasKumulatif($persenKualitasItemList);

        // Only call totalPersenCacatSupplier when there are received items;
        // the empty-state guard above already handles the zero case (Req 9.3).
        $totalCacat = $this->calc->totalPersenCacatSupplier($kumulatifQuality);

        $supplier->update([
            'total_persen_keterlambatan' => $persenKeterlambatan,
            'mean_hari_keterlambatan'    => $mean,
            'total_persen_cacat'         => $totalCacat,
        ]);
    }

    /**
     * Return all Supplier records that have at least one received PengadaanDetail.
     *
     * A detail is "received" when jumlah_diterima_baik IS NOT NULL AND
     * tanggal_kedatangan_aktual IS NOT NULL.
     *
     * Requirement: 13.2
     *
     * @return \Illuminate\Support\Collection<int, Supplier>
     */
    public function suppliersWithPerformance(): Collection
    {
        return Supplier::whereHas('header', function ($query) {
            $query->whereHas('detail', function ($q) {
                $q->whereNotNull('jumlah_diterima_baik')
                  ->whereNotNull('tanggal_kedatangan_aktual');
            });
        })->get();
    }
}
