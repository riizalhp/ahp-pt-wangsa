<?php

namespace App\Observers;

use App\Models\Pengadaan;
use App\Services\Supplier\RekapSupplierService;

class PengadaanObserver
{
    protected $rekapService;

    public function __construct(RekapSupplierService $rekapService)
    {
        $this->rekapService = $rekapService;
    }

    /**
     * Handle the Pengadaan "saved" event.
     */
    public function saved(Pengadaan $pengadaan): void
    {
        $this->rekapService->recalculateForSupplier($pengadaan->supplier_id);
    }

    /**
     * Handle the Pengadaan "deleted" event.
     */
    public function deleted(Pengadaan $pengadaan): void
    {
        $this->rekapService->recalculateForSupplier($pengadaan->supplier_id);
    }
}
