<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Pemilih;

/**
 * @property string $id
 * @property string $kode
 * @property string $nama
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Desa> $desas
 * @property-read int|null $desas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereUpdatedAt($value)
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
