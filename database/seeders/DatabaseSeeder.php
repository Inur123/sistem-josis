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
        // Step 2: Data wilayah (kecamatan + desa dari API wilayah.id)
        $this->call(WilayahSeeder::class);

        // Step 3: Akun user (admin, kecamatan, desa) — akan ditambahkan
        // $this->call(UserSeeder::class);
    }
}
