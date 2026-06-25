<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Default password sementara — wajib diganti setelah deploy pertama.
     */
    private const DEFAULT_ADMIN_PASSWORD = 'Admin@Josis2026!';

    private const DEFAULT_KECAMATAN_PASSWORD = 'Kecamatan@2026!';

    private const DEFAULT_DESA_PASSWORD = 'Desa@2026!';

    public function run(): void
    {
        $this->command->info('👤 Memulai seeder akun user...');
        $this->command->newLine();

        $this->seedAdmin();
        $this->seedKecamatan();
        $this->seedDesa();

        $this->command->newLine();
        $this->command->info('✅ Selesai! Ringkasan akun:');
        $this->command->table(
            ['Role', 'Jumlah'],
            [
                ['Admin', User::where('role', 'admin')->count()],
                ['Kecamatan', User::where('role', 'kecamatan')->count()],
                ['Desa', User::where('role', 'desa')->count()],
                ['Total', User::count()],
            ]
        );

        $this->command->newLine();
        $this->command->warn('⚠️  Semua password default WAJIB diganti setelah pertama kali login!');
    }

    /**
     * Buat 1 akun Admin.
     */
    private function seedAdmin(): void
    {
        $email = 'admin@josis.magetan.id';

        User::updateOrCreate(
            ['email_hash' => hash('sha256', $email)],
            [
                'name' => 'Administrator JOSIS',
                'email' => $email,
                'email_hash' => hash('sha256', $email),
                'password' => Hash::make(self::DEFAULT_ADMIN_PASSWORD),
                'role' => 'admin',
            ]
        );

        $this->command->info('✅ Admin: '.$email);
        $this->command->line('   Password: '.self::DEFAULT_ADMIN_PASSWORD);
        $this->command->newLine();
    }

    /**
     * Buat 4 akun Kecamatan (satu per kecamatan).
     */
    private function seedKecamatan(): void
    {
        $this->command->info('📍 Membuat akun kecamatan...');

        $kecamatans = Kecamatan::all();

        foreach ($kecamatans as $kecamatan) {
            $slug = Str::slug($kecamatan->nama, '');
            $email = "kec.{$slug}@josis.magetan.id";

            User::updateOrCreate(
                ['email_hash' => hash('sha256', $email)],
                [
                    'name' => 'Operator Kecamatan '.$kecamatan->nama,
                    'email' => $email,
                    'email_hash' => hash('sha256', $email),
                    'password' => Hash::make(self::DEFAULT_KECAMATAN_PASSWORD),
                    'role' => 'kecamatan',
                    'kecamatan_id' => $kecamatan->id,
                ]
            );

            $this->command->line("   ✅ {$email}");
        }

        $this->command->newLine();
    }

    /**
     * Buat akun Desa untuk setiap desa (50 akun).
     */
    private function seedDesa(): void
    {
        $this->command->info('🏘️  Membuat akun desa/kelurahan...');

        $desas = Desa::with('kecamatan')->get();

        foreach ($desas as $desa) {
            $slugDesa = Str::slug($desa->nama, '');
            $slugKec = Str::slug($desa->kecamatan->nama, '');
            $email = "desa.{$slugDesa}.{$slugKec}@josis.magetan.id";

            User::updateOrCreate(
                ['email_hash' => hash('sha256', $email)],
                [
                    'name' => 'Operator Desa '.$desa->nama,
                    'email' => $email,
                    'email_hash' => hash('sha256', $email),
                    'password' => Hash::make(self::DEFAULT_DESA_PASSWORD),
                    'role' => 'desa',
                    'kecamatan_id' => $desa->kecamatan_id,
                    'desa_id' => $desa->id,
                ]
            );

            $this->command->line("   ✅ {$email}");
        }

        $this->command->newLine();
    }
}
