<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_suaras', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tps_id')->unique()->constrained('tps')->cascadeOnDelete();
            $table->unsignedInteger('total_suara')->default(0);
            $table->string('c_hasil_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_suaras');
    }
};
