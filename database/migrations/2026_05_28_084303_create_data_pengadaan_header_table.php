<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the Purchase Order header table (data_pengadaan_header).
     * This is the parent side of the header/detail procurement structure
     * (Req 15.1). Each header belongs to one supplier (Req 1.8) and carries
     * a unique No_PO to guard against duplicates (Req 3.6).
     */
    public function up(): void
    {
        Schema::create('data_pengadaan_header', function (Blueprint $table) {
            $table->id(); // Pengadaan_ID
            $table->unsignedBigInteger('supplier_id');
            $table->string('no_po')->unique();          // Req 3.6 duplicate guard
            $table->date('tanggal_po');
            $table->date('tanggal_kedatangan_target');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('tanggal_po');
            $table->foreign('supplier_id')->references('id')->on('data_supplier')->onDelete('restrict'); // Req 1.8
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pengadaan_header');
    }
};
