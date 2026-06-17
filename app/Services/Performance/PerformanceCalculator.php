<?php

namespace App\Services\Performance;

/**
 * Pure, dependency-free service that owns all per-item and per-supplier
 * procurement performance math.
 *
 * It deliberately operates only on primitives and arrays (no Eloquent / no
 * database access) so it is directly unit- and property-testable. Controllers
 * and aggregators pass in raw values and receive computed metrics back.
 *
 * Zero-division is handled centrally: whenever a divisor would be zero (no
 * ordered quantity, no received items, no distinct Purchase Order), the
 * relevant method returns 0.0 / 0 rather than throwing
 * (Requirements 7.3, 8.3, 9.3).
 */
class PerformanceCalculator
{
    /**
     * Per-item quality percentage of a single received line item.
     *
     * Persen_Kualitas_Item = (Jumlah_Diterima_Baik / Jumlah_Dipesan) * 100.
     *
     * Returns 0.0 when the ordered quantity is zero or negative, so the
     * caller never divides by zero.
     *
     * Requirement 5.1.
     *
     * @param float $diterimaBaik Quantity of good (non-defective) units received.
     * @param float $dipesan      Ordered quantity for the line item.
     */
    public function persenKualitasItem(float $diterimaBaik, float $dipesan): float
    {
        if ($dipesan <= 0) {
            return 0.0;
        }

        return ($diterimaBaik / $dipesan) * 100.0;
    }

    /**
     * Per-item defect percentage, complementary to the quality percentage.
     *
     * Persen_Cacat_Item = 100 - Persen_Kualitas_Item.
     *
     * Requirement 5.2.
     *
     * @param float $persenKualitasItem The quality percentage of the line item.
     */
    public function persenCacatItem(float $persenKualitasItem): float
    {
        return 100.0 - $persenKualitasItem;
    }

    /**
     * Whole number of days a line item arrived after its target arrival date,
     * clamped at zero so it is never negative.
     *
     * Hari_Keterlambatan = max(0, whole_days(Tanggal_Kedatangan_Aktual − Tanggal_Kedatangan_Target)).
     *
     * The comparison is performed on calendar dates (time-of-day is stripped)
     * in UTC to avoid daylight-saving / timezone drift. When the actual arrival
     * date is on or before the target date the result is 0.
     *
     * Requirements 6.1, 6.2.
     *
     * @param \DateTimeInterface $target Expected (target) arrival date.
     * @param \DateTimeInterface $aktual Actual arrival date.
     */
    public function hariKeterlambatan(\DateTimeInterface $target, \DateTimeInterface $aktual): int
    {
        $utc = new \DateTimeZone('UTC');

        $targetTs = (new \DateTimeImmutable($target->format('Y-m-d') . ' 00:00:00', $utc))->getTimestamp();
        $aktualTs = (new \DateTimeImmutable($aktual->format('Y-m-d') . ' 00:00:00', $utc))->getTimestamp();

        $days = intdiv($aktualTs - $targetTs, 86400);

        return max(0, $days);
    }

    /**
     * Classify a received line item as late (Item_Terlambat).
     *
     * True exactly when Hari_Keterlambatan is greater than zero.
     *
     * Requirement 6.4.
     */
    public function isItemTerlambat(int $hariKeterlambatan): bool
    {
        return $hariKeterlambatan > 0;
    }

    /**
     * Supplier cumulative late-item percentage across received line items.
     *
     * Persen_Keterlambatan_Supplier =
     *   (count of items with Hari_Keterlambatan > 0 / count of received items) * 100.
     *
     * Returns 0.0 when the list is empty (no received items).
     *
     * Requirements 7.1, 7.3.
     *
     * @param array<int|string, int> $hariKeterlambatanList Lateness-in-days for every received line item.
     */
    public function persenKeterlambatanSupplier(array $hariKeterlambatanList): float
    {
        $total = count($hariKeterlambatanList);

        if ($total === 0) {
            return 0.0;
        }

        $terlambat = 0;
        foreach ($hariKeterlambatanList as $hari) {
            if ((int) $hari > 0) {
                $terlambat++;
            }
        }

        return $this->safeDivide((float) $terlambat, (float) $total) * 100.0;
    }

    /**
     * Supplier mean lateness across all received items.
     *
     * Mean_Hari_Keterlambatan =
     *   sum(Hari_Keterlambatan over all received items)
     *   / count(all received items).
     *
     * The divisor is the count of ALL received line items (detail produk),
     * NOT the count of distinct Purchase Orders.
     * Returns 0.0 when the list is empty.
     *
     * Requirements 8.1, 8.2, 8.3.
     *
     * @param array<int, array{pengadaan_id: int, hari_keterlambatan: int}> $receivedItems
     *        List of received-item rows, each shaped as
     *        ['pengadaan_id' => int, 'hari_keterlambatan' => int].
     */
    public function meanHariKeterlambatan(array $receivedItems): float
    {
        $totalItems = count($receivedItems);
        
        if ($totalItems === 0) {
            return 0.0;
        }

        $sumHari = 0;
        foreach ($receivedItems as $item) {
            $sumHari += (int) ($item['hari_keterlambatan'] ?? 0);
        }

        return $this->safeDivide((float) $sumHari, (float) $totalItems);
    }

    /**
     * Supplier cumulative average quality across received line items.
     *
     * Persen_Kualitas_Kumulatif =
     *   sum(Persen_Kualitas_Item) / count(received items).
     *
     * Returns 0.0 when the list is empty (no received items).
     *
     * Requirements 9.1, 9.3.
     *
     * @param array<int|string, float> $persenKualitasItemList Per-item quality percentages.
     */
    public function persenKualitasKumulatif(array $persenKualitasItemList): float
    {
        $count = count($persenKualitasItemList);

        if ($count === 0) {
            return 0.0;
        }

        $sum = 0.0;
        foreach ($persenKualitasItemList as $persen) {
            $sum += (float) $persen;
        }

        return $this->safeDivide($sum, (float) $count);
    }

    /**
     * Supplier cumulative defect percentage, complementary to cumulative quality.
     *
     * Total_Persen_Cacat_Supplier = 100 - Persen_Kualitas_Kumulatif.
     *
     * When cumulative quality is 0 (no received items) this returns 100.0 only
     * if invoked directly; the aggregator is responsible for treating the
     * "no received items" case as 0.0 for both values per Req 9.3.
     *
     * Requirement 9.2.
     *
     * @param float $persenKualitasKumulatif The supplier's cumulative quality percentage.
     */
    public function totalPersenCacatSupplier(float $persenKualitasKumulatif): float
    {
        return 100.0 - $persenKualitasKumulatif;
    }

    /**
     * Centralized guard against division by zero. Returns 0.0 when the divisor
     * is zero (or negative), otherwise the quotient. This keeps every metric
     * method free of throwing on empty inputs (Requirements 7.3, 8.3, 9.3).
     */
    private function safeDivide(float $numerator, float $denominator): float
    {
        if ($denominator <= 0.0) {
            return 0.0;
        }

        return $numerator / $denominator;
    }
}
