<?php

use App\Support\MigrationMapper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Data migration (Req 15.4): split every existing flat `data_pengadaan`
     * row into one `data_pengadaan_header` + one `data_pengadaan_detail`
     * pair, preserving the recorded order and receiving values. The legacy
     * `data_pengadaan` table is intentionally left in place so the migration
     * result can be verified before it is ever dropped.
     *
     * The per-row field mapping lives in the pure App\Support\MigrationMapper
     * helper, keeping this migration thin and the mapping logic independently
     * testable without any database dependency.
     *
     * The whole split runs inside a single transaction: if any row fails to
     * map/insert, everything rolls back so no partial header/detail set is
     * left behind.
     */
    public function up(): void
    {
        // Guard: skip gracefully if the legacy table was never created.
        if (! Schema::hasTable('data_pengadaan')) {
            return;
        }

        // Read each legacy flat row and join produk to obtain the unit (satuan).
        $rows = DB::table('data_pengadaan')
            ->leftJoin('data_produk', 'data_pengadaan.produk_id', '=', 'data_produk.id')
            ->select('data_pengadaan.*', 'data_produk.satuan as produk_satuan')
            ->orderBy('data_pengadaan.id')
            ->get();

        // Guard: nothing to migrate when the legacy table is empty.
        if ($rows->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($rows) {
            $now = now();

            foreach ($rows as $row) {
                $legacy = (array) $row;

                // The related produk's unit; default to empty string when the
                // produk row is missing so the (non-null) satuan column is satisfied.
                $satuan = $legacy['produk_satuan'] ?? '';
                unset($legacy['produk_satuan']);

                $mapped = MigrationMapper::mapFlatRow($legacy, (string) $satuan);

                $header = $mapped['header'];
                $header['created_at'] = $now;
                $header['updated_at'] = $now;

                $headerId = DB::table('data_pengadaan_header')->insertGetId($header);

                $detail = $mapped['detail'];
                $detail['pengadaan_id'] = $headerId;
                $detail['created_at']   = $now;
                $detail['updated_at']   = $now;

                DB::table('data_pengadaan_detail')->insert($detail);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * Removes only the migrated rows this migration created: deleting the
     * synthesized `PO/MIGRASI/%` headers cascades to their detail rows via the
     * data_pengadaan_detail foreign key (onDelete cascade). The legacy
     * `data_pengadaan` table is left untouched.
     */
    public function down(): void
    {
        if (! Schema::hasTable('data_pengadaan_header')) {
            return;
        }

        DB::transaction(function () {
            $migratedHeaderIds = DB::table('data_pengadaan_header')
                ->where('no_po', 'LIKE', 'PO/MIGRASI/%')
                ->pluck('id');

            if ($migratedHeaderIds->isEmpty()) {
                return;
            }

            // Remove detail rows explicitly first so the reversal is safe
            // regardless of whether the FK cascade fires (e.g. on engines
            // where FK enforcement is optional).
            if (Schema::hasTable('data_pengadaan_detail')) {
                DB::table('data_pengadaan_detail')
                    ->whereIn('pengadaan_id', $migratedHeaderIds)
                    ->delete();
            }

            DB::table('data_pengadaan_header')
                ->whereIn('id', $migratedHeaderIds)
                ->delete();
        });
    }
};
