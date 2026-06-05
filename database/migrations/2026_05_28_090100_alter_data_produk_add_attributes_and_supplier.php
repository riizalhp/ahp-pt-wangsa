<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds descriptive product attributes and a (nullable at the DB level)
     * supplier foreign key to data_produk. supplier_id is nullable here to
     * allow back-filling existing rows during data migration; the
     * mandatory-on-input rule is enforced later at the validation layer.
     *
     * Design reference: "Migration 2 — Alter data_produk" (Req 2.1, 2.6).
     */
    public function up(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->string('jenis_produk')->nullable()->after('nama');
            $table->string('merk')->nullable()->after('jenis_produk');
            $table->string('ukuran')->nullable()->after('merk');
            $table->string('kapasitas_pasokan')->nullable()->after('ukuran');
            $table->unsignedBigInteger('supplier_id')->nullable()->after('id');
            $table->foreign('supplier_id')->references('id')->on('data_supplier')->onDelete('restrict');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * The foreign key and index are dropped explicitly before the columns are
     * removed. This ordering is required on modern SQLite (>= 3.35), where
     * Laravel issues a native "ALTER TABLE ... DROP COLUMN" that SQLite refuses
     * while the column is still referenced by a foreign key. Issuing
     * dropForeign() forces Laravel's SQLite grammar to rebuild the table
     * (recreating it without the foreign key, index, and dropped columns) in a
     * single pass, and the same statements drop the constraint/index cleanly on
     * MySQL/MariaDB.
     */
    public function down(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'jenis_produk',
                'merk',
                'ukuran',
                'kapasitas_pasokan',
            ]);
        });
    }
};
