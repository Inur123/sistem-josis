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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('role', ['admin', 'kecamatan', 'desa'])->default('desa');
            $table->foreignUlid('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignUlid('desa_id')->nullable()->constrained('desas')->nullOnDelete();
            $table->text('name');
            $table->text('email');
            $table->string('email_hash', 64)->nullable()->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUlid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
