<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class WilayahSeeder extends Seeder
{
    /**
     * 4 Kecamatan target di Kabupaten Magetan (kode: 35.20)
     * Sumber: https://wilayah.id/api/districts/35.20.json
     */
    private const KECAMATAN_TARGET = [
        '35.20.12' => 'Barat',
        '35.20.13' => 'Karangrejo',
        '35.20.14' => 'Karas',
        '35.20.15' => 'Kartoharjo',
    ];

    /**
     * Seed kecamatan dan desa dari API wilayah.id
     */
    public function run(): void
    {
        $this->command->info('🗺️  Memulai seeder data wilayah dari wilayah.id...');
        $this->command->newLine();

        foreach (self::KECAMATAN_TARGET as $kodeKecamatan => $namaKecamatan) {
            // Buat/update data kecamatan
            $kecamatan = Kecamatan::updateOrCreate(
                ['kode' => $kodeKecamatan],
                ['nama' => $namaKecamatan],
            );

            $this->command->info("📍 Kecamatan: {$namaKecamatan} ({$kodeKecamatan})");

            // Ambil data desa dari API wilayah.id
            $this->seedDesas($kecamatan, $kodeKecamatan);

            $this->command->newLine();
        }

        $totalKecamatan = Kecamatan::count();
        $totalDesa = Desa::count();

        $this->command->info("✅ Selesai! Total: {$totalKecamatan} kecamatan, {$totalDesa} desa/kelurahan.");
    }

    /**
     * Ambil desa dari API dan simpan ke database.
     */
    private function seedDesas(Kecamatan $kecamatan, string $kodeKecamatan): void
    {
        $url = "https://wilayah.id/api/villages/{$kodeKecamatan}.json";

        $this->command->line("   Mengambil data dari: {$url}");

        try {
            $response = Http::timeout(15)->get($url);

            if (! $response->successful()) {
                $this->command->error("   ❌ Gagal ambil data: HTTP {$response->status()}");

                return;
            }

            $desas = $response->json('data', []);

            if (empty($desas)) {
                $this->command->warn("   ⚠️  Tidak ada desa ditemukan.");

                return;
            }

            $count = 0;

            foreach ($desas as $desaData) {
                Desa::updateOrCreate(
                    ['kode' => $desaData['code']],
                    [
                        'nama'          => $desaData['name'],
                        'kecamatan_id'  => $kecamatan->id,
                    ],
                );
                $count++;
            }

            $this->command->line("   ✅ {$count} desa/kelurahan berhasil disimpan.");

        } catch (\Exception $e) {
            $this->command->error("   ❌ Error: {$e->getMessage()}");
        }
    }
}
