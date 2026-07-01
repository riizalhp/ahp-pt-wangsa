<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. data_akun
        Schema::create('data_akun', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('nama');
            $table->enum('role', ['supervisor', 'sales', 'logistik']);
            $table->timestamps();
        });

        // 2. data_supplier
        Schema::create('data_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->double('mean_hari_keterlambatan')->default(0);
            $table->double('total_persen_cacat')->default(0);
            $table->double('total_persen_keterlambatan')->default(0);
            $table->timestamps();
        });

        // 3. data_produk
        Schema::create('data_produk', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->timestamps();
        });

        // 4. data_kriteria
        Schema::create('data_kriteria', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // 5. data_subkriteria
        Schema::create('data_subkriteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kriteria_id');
            $table->string('kode');
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['kriteria_id', 'kode']);
            $table->foreign('kriteria_id')->references('id')->on('data_kriteria')->onDelete('cascade');
        });

        // 6. penilaian_kriteria
        Schema::create('penilaian_kriteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('a_id');
            $table->unsignedBigInteger('b_id');
            $table->double('nilai');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['a_id', 'b_id']);
            $table->foreign('a_id')->references('id')->on('data_kriteria')->onDelete('cascade');
            $table->foreign('b_id')->references('id')->on('data_kriteria')->onDelete('cascade');
        });

        // 7. penilaian_subkriteria
        Schema::create('penilaian_subkriteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kriteria_id');
            $table->unsignedBigInteger('a_id');
            $table->unsignedBigInteger('b_id');
            $table->double('nilai');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['kriteria_id', 'a_id', 'b_id']);
            $table->foreign('kriteria_id')->references('id')->on('data_kriteria')->onDelete('cascade');
            $table->foreign('a_id')->references('id')->on('data_subkriteria')->onDelete('cascade');
            $table->foreign('b_id')->references('id')->on('data_subkriteria')->onDelete('cascade');
        });

        // 8. penilaian_supplier
        Schema::create('penilaian_supplier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subkriteria_id');
            $table->unsignedBigInteger('a_supplier_id');
            $table->unsignedBigInteger('b_supplier_id');
            $table->double('nilai');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['subkriteria_id', 'a_supplier_id', 'b_supplier_id'], 'penilaian_supplier_unique');
            $table->foreign('subkriteria_id')->references('id')->on('data_subkriteria')->onDelete('cascade');
            $table->foreign('a_supplier_id')->references('id')->on('data_supplier')->onDelete('cascade');
            $table->foreign('b_supplier_id')->references('id')->on('data_supplier')->onDelete('cascade');
        });

        // 9. data_hasil_ahp
        Schema::create('data_hasil_ahp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->double('nilai_akhir');
            $table->integer('ranking');
            $table->timestamp('computed_at')->useCurrent();

            $table->index('ranking');
            $table->foreign('supplier_id')->references('id')->on('data_supplier')->onDelete('cascade');
        });

        // 10. data_pengadaan
        Schema::create('data_pengadaan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('produk_id');
            $table->integer('jumlah_dibeli');
            $table->date('tanggal_po');
            $table->date('tanggal_kedatangan')->nullable();
            $table->integer('jumlah_diterima')->nullable();
            $table->integer('jumlah_cacat')->nullable();
            $table->double('persen_kualitas')->nullable();
            $table->integer('hari_keterlambatan')->nullable();
            $table->string('foto_path')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('tanggal_po');
            $table->foreign('supplier_id')->references('id')->on('data_supplier')->onDelete('restrict');
            $table->foreign('produk_id')->references('id')->on('data_produk')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_pengadaan');
        Schema::dropIfExists('data_hasil_ahp');
        Schema::dropIfExists('penilaian_supplier');
        Schema::dropIfExists('penilaian_subkriteria');
        Schema::dropIfExists('penilaian_kriteria');
        Schema::dropIfExists('data_subkriteria');
        Schema::dropIfExists('data_kriteria');
        Schema::dropIfExists('data_produk');
        Schema::dropIfExists('data_supplier');
        Schema::dropIfExists('data_akun');
    }
};
