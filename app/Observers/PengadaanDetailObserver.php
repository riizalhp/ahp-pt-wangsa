<?php

namespace App\Observers;

use App\Models\PengadaanDetail;
use App\Services\Supplier\SupplierMetricsService;

/**
 * Listens to write events on PengadaanDetail and triggers a supplier metrics
 * recomputation after each receiving-related save or delete.
 *
 * Requirements: 7.1, 8.1, 9.1
 */
class PengadaanDetailObserver
{
    public function __construct(private SupplierMetricsService $metricsService) {}

    /**
     * Handle the PengadaanDetail "saved" event.
     *
     * Recalculates the aggregated performance metrics for the supplier linked
     * to this detail's header whenever a detail row is created or updated.
     */
    public function saved(PengadaanDetail $detail): void
    {
        $header = $detail->header;

        if ($header) {
            $this->metricsService->recalculateForSupplier($header->supplier_id);
        }
    }

    /**
     * Handle the PengadaanDetail "deleted" event.
     *
     * Recalculates supplier aggregates after a detail row is removed so the
     * stored metrics reflect the remaining received items.
     */
    public function deleted(PengadaanDetail $detail): void
    {
        $header = $detail->header;

        if ($header) {
            $this->metricsService->recalculateForSupplier($header->supplier_id);
        }
    }
}
