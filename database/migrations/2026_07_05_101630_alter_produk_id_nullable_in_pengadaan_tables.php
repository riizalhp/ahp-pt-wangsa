<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Make produk_id nullable and change foreign key constraint from RESTRICT to SET NULL
     * in both data_pengadaan and data_pengadaan_detail tables.
     * This allows products to be deleted even if they are referenced in procurement records.
     * When a product is deleted, the produk_id will be set to NULL instead of preventing deletion.
     */
    public function up(): void
    {
        // Use raw SQL for reliable constraint modification
        
        // 1. Alter data_pengadaan table
        DB::statement('ALTER TABLE data_pengadaan DROP FOREIGN KEY data_pengadaan_produk_id_fkey');
        DB::statement('ALTER TABLE data_pengadaan MODIFY produk_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE data_pengadaan ADD CONSTRAINT data_pengadaan_produk_id_fkey 
                       FOREIGN KEY (produk_id) REFERENCES data_produk(id) 
                       ON DELETE SET NULL ON UPDATE CASCADE');
        
        // 2. Alter data_pengadaan_detail table
        DB::statement('ALTER TABLE data_pengadaan_detail DROP FOREIGN KEY data_pengadaan_detail_produk_id_foreign');
        DB::statement('ALTER TABLE data_pengadaan_detail MODIFY produk_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE data_pengadaan_detail ADD CONSTRAINT data_pengadaan_detail_produk_id_foreign 
                       FOREIGN KEY (produk_id) REFERENCES data_produk(id) 
                       ON DELETE SET NULL ON UPDATE RESTRICT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert data_pengadaan table
        DB::statement('ALTER TABLE data_pengadaan DROP FOREIGN KEY data_pengadaan_produk_id_fkey');
        DB::statement('ALTER TABLE data_pengadaan MODIFY produk_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE data_pengadaan ADD CONSTRAINT data_pengadaan_produk_id_fkey 
                       FOREIGN KEY (produk_id) REFERENCES data_produk(id) 
                       ON DELETE RESTRICT ON UPDATE CASCADE');
        
        // 2. Revert data_pengadaan_detail table
        DB::statement('ALTER TABLE data_pengadaan_detail DROP FOREIGN KEY data_pengadaan_detail_produk_id_foreign');
        DB::statement('ALTER TABLE data_pengadaan_detail MODIFY produk_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE data_pengadaan_detail ADD CONSTRAINT data_pengadaan_detail_produk_id_foreign 
                       FOREIGN KEY (produk_id) REFERENCES data_produk(id) 
                       ON DELETE RESTRICT ON UPDATE RESTRICT');
    }
};
