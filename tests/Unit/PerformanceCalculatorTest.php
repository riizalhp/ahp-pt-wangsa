<?php

namespace Tests\Unit;

use App\Services\Performance\PerformanceCalculator;
use Eris\Generators;
use Tests\PropertyTestCase;

/**
 * Property-based tests for the pure PerformanceCalculator service.
 *
 * Feature: procurement-supplier-management
 */
class PerformanceCalculatorTest extends PropertyTestCase
{
    private PerformanceCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new PerformanceCalculator();
    }

    /**
     * Property 7: Per-item quality and defect are complementary and bounded.
     * Validates: Requirements 5.1, 5.2, 5.3
     */
    public function testPerItemQualityAndDefectAreComplementaryAndBounded(): void
    {
        $this->forAll(
            Generators::choose(0, 100000),
            Generators::choose(1, 100000)
        )->then(function (int $diterimaInt, int $dipesanInt) {
            // Good received cannot exceed ordered.
            $dipesan = $dipesanInt / 100.0;
            $diterimaBaik = min($diterimaInt / 100.0, $dipesan);

            $kualitas = $this->calc->persenKualitasItem($diterimaBaik, $dipesan);
            $cacat = $this->calc->persenCacatItem($kualitas);

            // Bounded within [0, 100].
            $this->assertGreaterThanOrEqual(0.0, $kualitas);
            $this->assertLessThanOrEqual(100.0 + 1e-9, $kualitas);

            // Complementary: they sum to 100.
            $this->assertEqualsWithDelta(100.0, $kualitas + $cacat, 1e-9);
        });
    }

    /**
     * Per-item quality returns 0.0 when ordered quantity is zero or negative
     * (centralized zero-division guard).
     * Validates: Requirement 5.1
     */
    public function testPerItemQualityIsZeroWhenOrderedIsNonPositive(): void
    {
        $this->forAll(
            Generators::choose(0, 100000),
            Generators::choose(-100000, 0)
        )->then(function (int $diterimaInt, int $dipesanInt) {
            $kualitas = $this->calc->persenKualitasItem($diterimaInt / 100.0, $dipesanInt / 100.0);
            $this->assertSame(0.0, $kualitas);
        });
    }

    /**
     * Property 9: Lateness in days is the clamped, non-negative whole-day difference.
     * Validates: Requirements 6.1, 6.2, 6.3, 6.4
     */
    public function testLatenessIsClampedNonNegativeWholeDays(): void
    {
        $this->forAll(
            // Days offset of actual relative to target, in range [-30, 30].
            Generators::choose(-30, 30)
        )->then(function (int $offset) {
            $target = new \DateTimeImmutable('2026-01-15 00:00:00', new \DateTimeZone('UTC'));
            $aktual = $target->modify(($offset >= 0 ? '+' : '') . $offset . ' days');

            $hari = $this->calc->hariKeterlambatan($target, $aktual);

            // Never negative.
            $this->assertGreaterThanOrEqual(0, $hari);

            // Equals the offset when late, 0 otherwise.
            $expected = max(0, $offset);
            $this->assertSame($expected, $hari);

            // isItemTerlambat aligns with positive lateness.
            $this->assertSame($expected > 0, $this->calc->isItemTerlambat($hari));
        });
    }

    /**
     * Lateness ignores time-of-day (compares calendar dates only).
     * Validates: Requirement 6.2
     */
    public function testLatenessIgnoresTimeOfDay(): void
    {
        $this->forAll(
            Generators::choose(0, 23),
            Generators::choose(0, 23)
        )->then(function (int $hTarget, int $hAktual) {
            $target = new \DateTimeImmutable(sprintf('2026-03-10 %02d:30:00', $hTarget), new \DateTimeZone('UTC'));
            $aktual = new \DateTimeImmutable(sprintf('2026-03-12 %02d:30:00', $hAktual), new \DateTimeZone('UTC'));

            // 2 calendar days apart regardless of hour-of-day.
            $this->assertSame(2, $this->calc->hariKeterlambatan($target, $aktual));
        });
    }

    /**
     * Property 10: Supplier cumulative lateness percentage.
     * Validates: Requirements 7.1, 7.2, 7.3
     */
    public function testSupplierCumulativeLatenessPercentage(): void
    {
        $this->forAll(
            Generators::seq(Generators::choose(0, 30))
        )->then(function (array $hariList) {
            $persen = $this->calc->persenKeterlambatanSupplier($hariList);

            if (count($hariList) === 0) {
                // Empty -> zero-division guard returns 0.
                $this->assertSame(0.0, $persen);
                return;
            }

            $late = count(array_filter($hariList, fn ($h) => (int) $h > 0));
            $expected = ($late / count($hariList)) * 100.0;

            $this->assertEqualsWithDelta($expected, $persen, 1e-9);
            $this->assertGreaterThanOrEqual(0.0, $persen);
            $this->assertLessThanOrEqual(100.0 + 1e-9, $persen);
        });
    }

    /**
     * Property 11: Supplier mean lateness uses the distinct Purchase Order count.
     * Validates: Requirements 8.1, 8.2, 8.3
     */
    public function testSupplierMeanLatenessUsesDistinctPoCount(): void
    {
        $this->forAll(
            Generators::seq(
                Generators::tuple(
                    Generators::choose(1, 5),   // pengadaan_id (small range to force collisions)
                    Generators::choose(0, 30)   // hari_keterlambatan
                )
            )
        )->then(function (array $rows) {
            $items = array_map(
                fn ($r) => ['pengadaan_id' => $r[0], 'hari_keterlambatan' => $r[1]],
                $rows
            );

            $mean = $this->calc->meanHariKeterlambatan($items);

            if (count($items) === 0) {
                $this->assertSame(0.0, $mean);
                return;
            }

            $sum = array_sum(array_column($items, 'hari_keterlambatan'));
            $distinct = count(array_unique(array_column($items, 'pengadaan_id')));
            $expected = $distinct > 0 ? $sum / $distinct : 0.0;

            $this->assertEqualsWithDelta($expected, $mean, 1e-9);

            // Divisor is distinct PO count: mean >= simple per-item average.
            $perItemMean = $sum / count($items);
            $this->assertGreaterThanOrEqual($perItemMean - 1e-9, $mean);
        });
    }

    /**
     * Property 12: Supplier cumulative quality and defect are complementary.
     * Validates: Requirements 9.1, 9.2, 9.3
     */
    public function testSupplierCumulativeQualityAndDefectAreComplementary(): void
    {
        $this->forAll(
            Generators::seq(Generators::choose(0, 10000))
        )->then(function (array $rawList) {
            $qualityList = array_map(fn ($v) => $v / 100.0, $rawList);

            $kumulatif = $this->calc->persenKualitasKumulatif($qualityList);

            if (count($qualityList) === 0) {
                $this->assertSame(0.0, $kumulatif);
                return;
            }

            $expected = array_sum($qualityList) / count($qualityList);
            $this->assertEqualsWithDelta($expected, $kumulatif, 1e-9);

            // Complementary with cumulative defect.
            $cacat = $this->calc->totalPersenCacatSupplier($kumulatif);
            $this->assertEqualsWithDelta(100.0, $kumulatif + $cacat, 1e-9);
        });
    }
}
