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
        // 1. Hapus unique index pada email (akan diganti email_hash)
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        // 2. Modifikasi kolom dan tambah kolom baru
        Schema::table('users', function (Blueprint $table) {
            // Ubah name & email ke text karena nilai terenkripsi AES-256-GCM cukup panjang
            $table->text('name')->change();
            $table->text('email')->change();

            // Tambah setelah id: role akun
            $table->enum('role', ['admin', 'kecamatan', 'desa'])
                ->default('desa')
                ->after('id');

            // FK ke kecamatans (diisi jika role = kecamatan)
            $table->foreignUlid('kecamatan_id')
                ->nullable()
                ->after('role')
                ->constrained('kecamatans')
                ->nullOnDelete();

            // FK ke desas (diisi jika role = desa)
            $table->foreignUlid('desa_id')
                ->nullable()
                ->after('kecamatan_id')
                ->constrained('desas')
                ->nullOnDelete();

            // email_hash: SHA-256 dari email asli — untuk login lookup & unique check
            $table->string('email_hash', 64)
                ->nullable()
                ->unique()
                ->after('email');

            // Hapus remember_token — tidak dipakai (tidak ada "Remember me")
            $table->dropColumn('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 255)->change();
            $table->string('name', 255)->change();
            $table->unique('email');
            $table->rememberToken();

            $table->dropForeign(['kecamatan_id']);
            $table->dropForeign(['desa_id']);
            $table->dropColumn([
                'role',
                'kecamatan_id',
                'desa_id',
                'email_hash',
            ]);
        });
    }
};
