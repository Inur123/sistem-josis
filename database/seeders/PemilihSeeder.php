<?php

namespace Database\Seeders;

use App\Models\AnggotaTim;
use App\Models\Pemilih;
use App\Models\User;
use Illuminate\Database\Seeder;

class PemilihSeeder extends Seeder
{
    private const PEMILIH_PER_RELAWAN = 100;

    /** Nama depan untuk data dummy */
    private const NAMA_DEPAN_L = [
        'Ahmad', 'Budi', 'Cahyo', 'Dani', 'Eko', 'Fajar', 'Gilang', 'Hendra',
        'Irwan', 'Joko', 'Krisna', 'Lukman', 'Muji', 'Nanang', 'Okta', 'Prapto',
        'Rizki', 'Slamet', 'Tono', 'Usman', 'Wahyu', 'Yusuf', 'Zaenal', 'Agus',
        'Bayu', 'Dedi', 'Fandi', 'Gatot', 'Hadi', 'Imam',
    ];

    private const NAMA_DEPAN_P = [
        'Ani', 'Bela', 'Citra', 'Dewi', 'Eni', 'Fitri', 'Gita', 'Hani',
        'Indah', 'Juwita', 'Kartini', 'Lina', 'Maya', 'Nisa', 'Okti', 'Putri',
        'Rina', 'Sari', 'Tini', 'Umi', 'Vina', 'Wati', 'Yuni', 'Zulfa',
        'Anis', 'Bunga', 'Dian', 'Fara', 'Gina', 'Hesti',
    ];

    private const NAMA_BELAKANG = [
        'Santoso', 'Wijaya', 'Kusuma', 'Rahayu', 'Supriyanto', 'Wibowo',
        'Hidayat', 'Prasetyo', 'Saputra', 'Nugroho', 'Purnomo', 'Susanto',
        'Wahyudi', 'Setiawan', 'Firmansyah', 'Ardianto', 'Kurniawan', 'Gunawan',
        'Hartono', 'Lestari', 'Ningrum', 'Sulistyowati', 'Pertiwi', 'Anggraeni',
        'Handayani', 'Maharani', 'Utami', 'Sanjaya', 'Pratama', 'Ramadhan',
    ];

    private const JALAN = [
        'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Diponegoro', 'Jl. Ahmad Yani',
        'Jl. Gatot Subroto', 'Jl. Veteran', 'Jl. Pahlawan', 'Jl. Pemuda',
        'Jl. Kartini', 'Jl. Imam Bonjol', 'Jl. Cut Nyak Dien', 'Jl. Hassanudin',
        'Jl. Kapten Tendean', 'Jl. Sam Ratulangi', 'Jl. Pattimura',
    ];

    public function run(): void
    {
        $this->command->info('🗳️  Memulai seeder data pemilih...');

        $relawans = AnggotaTim::with(['desa', 'kecamatan'])
            ->where('role', 'relawan')
            ->get();

        if ($relawans->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada relawan ditemukan. Jalankan TimSeeder terlebih dahulu.');
            return;
        }

        // Cache user desa: desa_id → user
        $userDesaMap = User::where('role', 'desa')
            ->get()
            ->keyBy('desa_id');

        $this->command->info("   Ditemukan {$relawans->count()} relawan. Membuat " . self::PEMILIH_PER_RELAWAN . " pemilih per relawan...");

        $nikCounter  = 1000000;
        $totalInsert = 0;

        $bar = $this->command->getOutput()->createProgressBar($relawans->count());
        $bar->start();

        foreach ($relawans as $relawan) {
            $desaId      = $relawan->desa_id;
            $kecamatanId = $relawan->kecamatan_id;
            $userDesa    = $userDesaMap->get($desaId);
            $userId      = $userDesa?->id ?? null;

            $desaNama = $relawan->desa?->nama ?? 'Desa';

            for ($i = 1; $i <= self::PEMILIH_PER_RELAWAN; $i++) {
                $jk = ($i % 2 === 0) ? 'P' : 'L';

                $namaDepan = $jk === 'L'
                    ? self::NAMA_DEPAN_L[array_rand(self::NAMA_DEPAN_L)]
                    : self::NAMA_DEPAN_P[array_rand(self::NAMA_DEPAN_P)];

                $namaBelakang = self::NAMA_BELAKANG[array_rand(self::NAMA_BELAKANG)];
                $nama         = "{$namaDepan} {$namaBelakang}";

                // NIK: 16 digit unik (kode wilayah dummy + urutan)
                $nik = '3520' . str_pad((string) $nikCounter++, 12, '0', STR_PAD_LEFT);

                $jalan  = self::JALAN[array_rand(self::JALAN)];
                $nomor  = rand(1, 150);
                $rt     = str_pad((string) rand(1, 6), 3, '0', STR_PAD_LEFT);
                $rw     = str_pad((string) rand(1, 4), 3, '0', STR_PAD_LEFT);
                $alamat = "{$jalan} No. {$nomor}, Desa {$desaNama}";

                Pemilih::create([
                    'nik'           => $nik,
                    'nama'          => $nama,
                    'jenis_kelamin' => $jk,
                    'alamat'        => $alamat,
                    'rt'            => $rt,
                    'rw'            => $rw,
                    'desa_id'       => $desaId,
                    'kecamatan_id'  => $kecamatanId,
                    'user_id'       => $userId,
                    'relawan_id'    => $relawan->id,
                ]);

                $totalInsert++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("✅ Selesai! Total {$totalInsert} pemilih berhasil di-seed.");
    }
}
