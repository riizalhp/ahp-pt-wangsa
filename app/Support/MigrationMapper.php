<?php

namespace App\Support;

/**
 * MigrationMapper
 *
 * Pure, dependency-free helper that maps a legacy flat `data_pengadaan` row
 * into the new header/detail structure (Req 15.4). It performs NO database
 * access so it can be unit/property-tested in isolation (see task 2.6).
 *
 * The flat-to-header/detail field mapping is defined in the design document
 * ("Migration 5 — Data migration of existing flat rows").
 */
class MigrationMapper
{
    /**
     * Map a single legacy flat `data_pengadaan` row into a structured
     * ['header' => [...], 'detail' => [...]] array.
     *
     * Expected legacy row keys (associative array):
     *   id, supplier_id, produk_id, jumlah_dibeli, tanggal_po,
     *   tanggal_kedatangan, jumlah_diterima, jumlah_cacat, persen_kualitas,
     *   hari_keterlambatan, catatan
     *
     * The `satuan` is passed separately because it belongs to the related
     * produk, not the legacy pengadaan row itself.
     *
     * Mapping rules:
     *   header.supplier_id                = row.supplier_id
     *   header.no_po                      = "PO/MIGRASI/{id}" (synthesized, unique)
     *   header.tanggal_po                 = row.tanggal_po
     *   header.tanggal_kedatangan_target  = row.tanggal_kedatangan
     *   header.catatan                    = row.catatan (nullable)
     *   detail.produk_id                  = row.produk_id
     *   detail.jumlah_dipesan             = row.jumlah_dibeli (int -> decimal)
     *   detail.satuan                     = $satuan (from related produk)
     *   detail.jumlah_diterima_baik       = row.jumlah_diterima - row.jumlah_cacat
     *                                       (good units; null when jumlah_diterima is null)
     *   detail.tanggal_kedatangan_aktual  = row.tanggal_kedatangan
     *   detail.persen_kualitas_item       = row.persen_kualitas
     *   detail.hari_keterlambatan         = row.hari_keterlambatan
     *
     * @param  array<string, mixed>  $row     A legacy data_pengadaan row.
     * @param  string                $satuan  The unit copied from the related produk.
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
     * Convert an integer ordered quantity into a decimal value, preserving null.
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
     * Compute good (non-defective) received units = received - defective,
     * clamped at 0 so the result is never negative.
     *
     * Returns null when the received quantity is null (goods not yet received).
     * A null defective count is treated as zero.
     *
     * @param  mixed  $diterima  jumlah_diterima
     * @param  mixed  $cacat     jumlah_cacat
     * @return float|null
     */
    private static function goodUnits($diterima, $cacat): ?float
    {
        if ($diterima === null || $diterima === '') {
            return null;
        }

        $cacatValue = ($cacat === null || $cacat === '') ? 0 : $cacat;

        return max(0.0, (float) $diterima - (float) $cacatValue);
    }
}
