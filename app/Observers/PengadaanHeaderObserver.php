<?php

namespace App\Observers;

use App\Models\PengadaanHeader;
use App\Services\Supplier\SupplierMetricsService;

class PengadaanHeaderObserver
{
    public function __construct(private SupplierMetricsService $metricsService) {}

    public function deleted(PengadaanHeader $header): void
    {
        if ($header->supplier_id) {
            $this->metricsService->recalculateForSupplier($header->supplier_id);
        }
    }
}
