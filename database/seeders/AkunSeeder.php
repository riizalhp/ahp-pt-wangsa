<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akun;
use Illuminate\Support\Facades\Hash;

class AkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Akun::create([
            'username' => 'spv1',
            'password_hash' => Hash::make('password'),
            'nama' => 'Supervisor Procurement',
            'role' => 'supervisor',
        ]);

        Akun::create([
            'username' => 'sales1',
            'password_hash' => Hash::make('password'),
            'nama' => 'Sales Marketing',
            'role' => 'sales',
        ]);

        Akun::create([
            'username' => 'log1',
            'password_hash' => Hash::make('password'),
            'nama' => 'Staff Logistik',
            'role' => 'logistik',
        ]);
    }
}
