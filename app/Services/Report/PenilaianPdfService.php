<?php

namespace App\Services\Report;

use App\Models\HasilAhp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PenilaianPdfService
{
    /**
     * Check whether any AHP ranking results exist.
     *
     * @return bool
     */
    public function hasRanking(): bool
    {
        return HasilAhp::count() > 0;
    }

    /**
     * Retrieve all AHP ranking results ordered by ranking position ascending,
     * with the associated supplier eager-loaded.
     *
     * @return Collection
     */
    public function getRankings(): Collection
    {
        return HasilAhp::with('supplier')
            ->orderBy('ranking', 'asc')
            ->get();
    }

    /**
     * Generate a downloadable PDF of the Hasil Penilaian (AHP ranking results).
     *
     * Renders the Blade view 'pdf.hasil_penilaian' with the ranked supplier
     * data and the company name, then returns a download response.
     *
     * Requirements: 11.2, 11.3
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(): \Illuminate\Http\Response
    {
        $rankings    = $this->getRankings();
        $companyName = 'PT Wangsa Jatra Lestari';

        $pdf = Pdf::loadView('pdf.hasil_penilaian', compact('rankings', 'companyName'));

        return $pdf->download('hasil_penilaian.pdf');
    }
}
