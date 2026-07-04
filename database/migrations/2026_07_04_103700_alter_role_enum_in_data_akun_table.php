<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite check constraints enforce the ENUM values created at table definitions.
        // We drop the constraint or modify the table schema for SQLite.
        if (DB::getDriverName() === 'sqlite') {
            DB::table('data_akun')
                ->where('role', 'sales')
                ->update(['role' => 'admin_purchasing']);
            return;
        }

        // Step 1: Change role column to VARCHAR temporarily
        DB::statement("ALTER TABLE data_akun MODIFY COLUMN role VARCHAR(50)");
        
        // Step 2: Update 'sales' to 'admin_purchasing'
        DB::table('data_akun')
            ->where('role', 'sales')
            ->update(['role' => 'admin_purchasing']);
        
        // Step 3: Change back to ENUM with new values
        DB::statement("ALTER TABLE data_akun MODIFY COLUMN role ENUM('supervisor', 'admin_purchasing', 'logistik')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::table('data_akun')
                ->where('role', 'admin_purchasing')
                ->update(['role' => 'sales']);
            return;
        }

        // Step 1: Change to VARCHAR
        DB::statement("ALTER TABLE data_akun MODIFY COLUMN role VARCHAR(50)");
        
        // Step 2: Revert 'admin_purchasing' back to 'sales'
        DB::table('data_akun')
            ->where('role', 'admin_purchasing')
            ->update(['role' => 'sales']);
        
        // Step 3: Change back to original ENUM
        DB::statement("ALTER TABLE data_akun MODIFY COLUMN role ENUM('supervisor', 'sales', 'logistik')");
    }
};
