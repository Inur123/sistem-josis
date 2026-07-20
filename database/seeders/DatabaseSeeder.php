<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Step 1: Data wilayah (kecamatan + desa)
        $this->call(WilayahSeeder::class);

        // Step 2: Akun user (admin, kecamatan, desa)
        $this->call(UserSeeder::class);

        // Data tim dan pemilih tidak di-seed (isi manual via aplikasi)
        // $this->call(TimSeeder::class);
        // $this->call(PemilihSeeder::class);
    }
}
