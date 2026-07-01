<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the Purchase Order detail table (data_pengadaan_detail).
     * This is the child side of the header/detail procurement structure
     * (Req 15.2, 15.3). Each detail belongs to exactly one
     * data_pengadaan_header (cascade on delete) and references one
     * data_produk (restrict on delete, Req 2.8).
     *
     * Receiving and computed columns (jumlah_diterima_baik,
     * tanggal_kedatangan_aktual, persen_kualitas_item, hari_keterlambatan)
     * are nullable because a line item exists before goods are received.
     */
    public function up(): void
    {
        Schema::create('data_pengadaan_detail', function (Blueprint $table) {
            $table->id(); // Detail_ID
            $table->unsignedBigInteger('pengadaan_id');
            $table->unsignedBigInteger('produk_id');
            $table->decimal('jumlah_dipesan', 10, 2);                   // Req 3.3
            $table->string('satuan');                                   // Req 3.4
            $table->decimal('jumlah_diterima_baik', 10, 2)->nullable(); // Req 4.3
            $table->date('tanggal_kedatangan_aktual')->nullable();
            $table->double('persen_kualitas_item')->nullable();         // Req 5.4
            $table->integer('hari_keterlambatan')->nullable();
            $table->timestamps();

            $table->index('pengadaan_id');
            $table->index('produk_id');
            $table->foreign('pengadaan_id')->references('id')->on('data_pengadaan_header')->onDelete('cascade');
            $table->foreign('produk_id')->references('id')->on('data_produk')->onDelete('restrict'); // Req 2.8
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pengadaan_detail');
    }
};
