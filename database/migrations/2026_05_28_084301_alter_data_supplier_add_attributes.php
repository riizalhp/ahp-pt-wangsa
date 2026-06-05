<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds descriptive supplier attributes to data_supplier. All columns are
     * nullable. No_Telp maps to the existing `telepon` column, so no new
     * telephone column is added here.
     *
     * Design reference: "Migration 1 — Alter data_supplier" (Req 1.1, 1.5, 15.1).
     */
    public function up(): void
    {
        Schema::table('data_supplier', function (Blueprint $table) {
            $table->string('jenis_barang')->nullable()->after('nama');
            $table->string('kontak_person')->nullable()->after('alamat');
            $table->string('lama_kerja_sama')->nullable()->after('telepon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops the added descriptive columns. These columns carry no foreign
     * keys or named indexes, so a plain dropColumn works on both SQLite
     * (table rebuild) and MySQL/MariaDB.
     */
    public function down(): void
    {
        Schema::table('data_supplier', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_barang',
                'kontak_person',
                'lama_kerja_sama',
            ]);
        });
    }
};
