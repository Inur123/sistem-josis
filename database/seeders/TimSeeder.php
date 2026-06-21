<?php

namespace Database\Seeders;

use App\Models\AnggotaTim;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class TimSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Memulai seeder data anggota tim...');

        $kecamatans = Kecamatan::all();
        foreach ($kecamatans as $kec) {
            for ($i = 1; $i <= 2; $i++) {
                AnggotaTim::create([
                    'role' => 'korcam',
                    'kecamatan_id' => $kec->id,
                    'desa_id' => null,
                    'nama' => "Korcam {$kec->nama} {$i}",
                    'nik' => '352012' . str_pad((string) rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                    'no_hp' => '0812' . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'alamat' => "Alamat Korcam {$kec->nama} {$i}",
                ]);
            }
        }

        $desas = Desa::all();
        foreach ($desas as $desa) {
            // Seed 2 Kordes
            for ($i = 1; $i <= 2; $i++) {
                AnggotaTim::create([
                    'role' => 'kordes',
                    'kecamatan_id' => $desa->kecamatan_id,
                    'desa_id' => $desa->id,
                    'nama' => "Kordes {$desa->nama} {$i}",
                    'nik' => '352012' . str_pad((string) rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                    'no_hp' => '0812' . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'alamat' => "Alamat Kordes {$desa->nama} {$i}",
                ]);
            }

            // Seed 2 Relawan
            for ($i = 1; $i <= 2; $i++) {
                AnggotaTim::create([
                    'role' => 'relawan',
                    'kecamatan_id' => $desa->kecamatan_id,
                    'desa_id' => $desa->id,
                    'nama' => "Relawan {$desa->nama} {$i}",
                    'nik' => '352012' . str_pad((string) rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
                    'no_hp' => '0812' . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'alamat' => "Alamat Relawan {$desa->nama} {$i}",
                ]);
            }
        }

        $this->command->info('✅ Selesai! Data tim berhasil di-seed.');
    }
}
