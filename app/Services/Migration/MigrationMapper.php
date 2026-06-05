<?php

namespace App\Services\Migration;

/**
 * MigrationMapper
 *
 * Pure, dependency-free helper that maps a legacy flat `data_pengadaan` row
 * into the new header/detail structure (Req 15.4). It performs NO database
 * access and uses NO Eloquent, so it can be unit/property-tested in isolation
 * (see optional task 2.6).
 *
 * The flat-to-header/detail field mapping is defined in the design document
 * ("Migration 5 — Data migration of existing flat rows"). The method is
 * deterministic: the same input always produces the same output.
 */
class MigrationMapper
{
    /**
     * Map a single legacy flat `data_pengadaan` row, plus the related product's
     * unit (`satuan`), into a structured `['header' => [...], 'detail' => [...]]`
     * array.
     *
     * Expected legacy row keys (associative array):
     *   id, supplier_id, produk_id, jumlah_dibeli, tanggal_po,
     *   tanggal_kedatangan, jumlah_diterima, jumlah_cacat, persen_kualitas,
     *   hari_keterlambatan, catatan
     *
     * The product unit is passed separately (it lives on the related
     * `data_produk` row, not on the legacy procurement row) to keep this helper
     * free of any database/relationship lookups.
     *
     * Mapping rules (Req 15.4):
     *   header.supplier_id                = row.supplier_id
     *   header.no_po                      = "PO/MIGRASI/{id}" (synthesized, unique)
     *   header.tanggal_po                 = row.tanggal_po
     *   header.tanggal_kedatangan_target  = row.tanggal_kedatangan
     *   header.catatan                    = row.catatan
     *   detail.produk_id                  = row.produk_id
     *   detail.jumlah_dipesan             = (float) row.jumlah_dibeli
     *   detail.satuan                     = $satuan (from related produk)
     *   detail.jumlah_diterima_baik       = row.jumlah_diterima - row.jumlah_cacat
     *                                       (good units; null unless BOTH are present)
     *   detail.tanggal_kedatangan_aktual  = row.tanggal_kedatangan
     *   detail.persen_kualitas_item       = row.persen_kualitas
     *   detail.hari_keterlambatan         = row.hari_keterlambatan
     *
     * @param  array<string, mixed>  $row     A legacy `data_pengadaan` row.
     * @param  string                $satuan  The related produk's unit of measure.
     * @return array{header: array<string, mixed>, detail: array<string, mixed>}
     */
    public static function mapFlatRow(array $row, string $satuan): array
    {
        $id = $row['id'] ?? null;

        $header = [
            'supplier_id'               => $row['supplier_id'] ?? null,
            'no_po'                     => 'PO/MIGRASI/' . $id,
            'tanggal_po'                => $row['tanggal_po'] ?? null,
            'tanggal_kedatangan_target' => $row['tanggal_kedatangan'] ?? null,
            'catatan'                   => $row['catatan'] ?? null,
        ];

        $detail = [
            'produk_id'                 => $row['produk_id'] ?? null,
            'jumlah_dipesan'            => self::toDecimal($row['jumlah_dibeli'] ?? null),
            'satuan'                    => $satuan,
            'jumlah_diterima_baik'      => self::goodUnits($row['jumlah_diterima'] ?? null, $row['jumlah_cacat'] ?? null),
            'tanggal_kedatangan_aktual' => $row['tanggal_kedatangan'] ?? null,
            'persen_kualitas_item'      => $row['persen_kualitas'] ?? null,
            'hari_keterlambatan'        => $row['hari_keterlambatan'] ?? null,
        ];

        return [
            'header' => $header,
            'detail' => $detail,
        ];
    }

    /**
     * Convert an ordered quantity into a decimal (float) value, preserving null.
     *
     * The legacy column was an integer; the new `jumlah_dipesan` column is a
     * decimal(10,2), so the value is cast to float.
     *
     * @param  mixed  $value
     * @return float|null
     */
    private static function toDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * Compute good (non-defective) received units = received - defective.
     *
     * Per the mapping rules, the good-received quantity is only known when BOTH
     * the received quantity and the defective quantity are present; if either is
     * absent (goods not yet received / incomplete legacy data), the result is
     * null.
     *
     * @param  mixed  $diterima  jumlah_diterima
     * @param  mixed  $cacat     jumlah_cacat
     * @return float|null
     */
    private static function goodUnits($diterima, $cacat): ?float
    {
        $diterimaPresent = $diterima !== null && $diterima !== '';
        $cacatPresent    = $cacat !== null && $cacat !== '';

        if (! $diterimaPresent || ! $cacatPresent) {
            return null;
        }

        return (float) $diterima - (float) $cacat;
    }
}
