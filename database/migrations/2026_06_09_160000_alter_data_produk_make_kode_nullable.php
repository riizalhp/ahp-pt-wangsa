<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->string('kode')->nullable()->change();
            $table->string('satuan')->nullable()->change();
            $table->decimal('harga', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_produk', function (Blueprint $table) {
            $table->string('kode')->unique()->change();
            $table->string('satuan')->change();
            $table->decimal('harga', 15, 2)->change();
        });
    }
};
