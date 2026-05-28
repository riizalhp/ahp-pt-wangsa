<?php

namespace App\Services\Supplier;

use App\Models\Pengadaan;
use App\Models\Supplier;

class RekapSupplierService
{
    /**
     * Recalculate statistics for a supplier and update data_supplier.
     *
     * @param int $supplierId
     * @return void
     */
    public function recalculateForSupplier(int $supplierId): void
    {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return;
        }

        // Get actual deliveries (where tanggal_kedatangan is not null)
        $rows = Pengadaan::where('supplier_id', $supplierId)
            ->whereNotNull('tanggal_kedatangan')
            ->get();

        if ($rows->isEmpty()) {
            $supplier->update([
                'mean_hari_keterlambatan' => 0.0,
                'total_persen_cacat' => 0.0,
                'total_persen_keterlambatan' => 0.0,
            ]);
        } else {
            $totalDibeli = $rows->sum('jumlah_dibeli');
            $totalCacat = $rows->sum('jumlah_cacat');
            $totalTerlambat = $rows->filter(fn($r) => $r->hari_keterlambatan > 0)->count();
            $sumHari = $rows->sum('hari_keterlambatan');
            $count = $rows->count();

            $meanHari = $count > 0 ? (double)$sumHari / $count : 0.0;
            $persenCacat = $totalDibeli > 0 ? ((double)$totalCacat / $totalDibeli) * 100.0 : 0.0;
            $persenTerlambat = $count > 0 ? ((double)$totalTerlambat / $count) * 100.0 : 0.0;

            $supplier->update([
                'mean_hari_keterlambatan' => $meanHari,
                'total_persen_cacat' => $persenCacat,
                'total_persen_keterlambatan' => $persenTerlambat,
            ]);
        }
    }
}
