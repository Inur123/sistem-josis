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
        Schema::create('anggota_tim', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('role', ['korcam', 'kordes', 'relawan']);
            $table->foreignUlid('kecamatan_id')->nullable()->constrained('kecamatans')->cascadeOnDelete();
            $table->foreignUlid('desa_id')->nullable()->constrained('desas')->cascadeOnDelete();
            $table->string('nama');
            $table->text('nik')->nullable();
            $table->text('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_tim');
    }
};
