<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemilihs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Data sensitif — disimpan terenkripsi AES-256-GCM di database
            $table->text('nik');                       // 🔐 AES-256-GCM
            $table->string('nik_hash', 64)->unique();  // SHA-256 dari NIK asli (untuk cek duplikat)
            $table->text('nama');                      // 🔐 AES-256-GCM
            $table->text('jenis_kelamin');             // 🔐 AES-256-GCM  (L / P)
            $table->text('alamat');                    // 🔐 AES-256-GCM
            $table->text('rt');                        // 🔐 AES-256-GCM
            $table->text('rw');                        // 🔐 AES-256-GCM
            $table->text('foto_ktp')->nullable();      // Path file foto KTP terenkripsi

            // Relasi wilayah (tidak dienkripsi — hanya ID)
            $table->foreignUlid('desa_id')
                ->constrained('desas')
                ->cascadeOnDelete();

            $table->foreignUlid('kecamatan_id')
                ->constrained('kecamatans')
                ->cascadeOnDelete();

            // Siapa yang menginput (akun desa)
            $table->foreignUlid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUlid('relawan_id')
                ->constrained('anggota_tim')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('belum_verifikasi');
            $table->text('alasan_ditolak')->nullable();
            $table->foreignUlid('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilihs');
    }
};
