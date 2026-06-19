<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pemilih;

/**
 * @mixin \Eloquent
 */
class Kecamatan extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['kode', 'nama'];

    /**
     * Relasi ke desas.
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class);
    }

    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class);
    }

    /**
     * Relasi ke users (akun kecamatan).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
