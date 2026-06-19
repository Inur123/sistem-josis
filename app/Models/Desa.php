<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Eloquent
 */
class Desa extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['kode', 'nama', 'kecamatan_id'];

    /**
     * Relasi ke kecamatan.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Relasi ke users (akun desa).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke data pemilih.
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class);
    }
}
