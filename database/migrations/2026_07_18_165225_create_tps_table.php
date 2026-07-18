<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tps', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('nama', 100);
            $table->foreignUlid('desa_id')->constrained('desas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tps');
    }
};
