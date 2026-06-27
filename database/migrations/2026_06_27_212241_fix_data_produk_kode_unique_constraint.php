<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the unique constraint on data_produk.kode column.
     * It removes duplicate entries and ensures the unique constraint is properly set.
     */
    public function up(): void
    {
        // Step 1: Find and handle duplicate kode entries
        $duplicates = DB::table('data_produk')
            ->select('kode', DB::raw('COUNT(*) as count'))
            ->whereNotNull('kode')
            ->groupBy('kode')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Keep the first record, update others with new unique codes
            $records = DB::table('data_produk')
                ->where('kode', $duplicate->kode)
                ->orderBy('id')
                ->get();

            // Skip the first record (keep it)
            foreach ($records->skip(1) as $record) {
                // Generate new unique code
                $newKode = $this->generateUniqueKode();
                DB::table('data_produk')
                    ->where('id', $record->id)
                    ->update(['kode' => $newKode]);
            }
        }

        // Step 2: Drop the old unique constraint if it exists
        try {
            Schema::table('data_produk', function (Blueprint $table) {
                $table->dropUnique('data_produk_kode_unique');
            });
        } catch (\Exception $e) {
            // Constraint might not exist or have different name, continue
        }

        // Step 3: Recreate the unique constraint (allowing nulls)
        Schema::table('data_produk', function (Blueprint $table) {
            $table->unique('kode', 'data_produk_kode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep the constraint on rollback
        // No action needed as the constraint should remain
    }

    /**
     * Generate a unique product code
     */
    private function generateUniqueKode(): string
    {
        $lastProduk = DB::table('data_produk')
            ->where('kode', 'LIKE', 'P%')
            ->orderByRaw('CAST(SUBSTRING(kode, 2) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastProduk && preg_match('/P(\d+)/', $lastProduk->kode, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        do {
            $newKode = 'P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = DB::table('data_produk')->where('kode', $newKode)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $newKode;
    }
};
