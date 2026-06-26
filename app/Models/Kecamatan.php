<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $kode
 * @property string $nama
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, Desa> $desas
 * @property-read int|null $desas_count
 * @property-read Collection<int, Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kecamatan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Kecamatan extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory, HasUlids;

    protected $fillable = ['kode', 'nama'];

    /**
     * Relasi ke desas.
     */
    /**
     * @return HasMany<Pemilih, $this>
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class);
    }

    /**
     * @return HasMany<Desa, $this>
     */
    public function desas(): HasMany
    {
        return $this->hasMany(Desa::class);
    }

    /**
     * Relasi ke users (akun kecamatan).
     */
    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relasi ke anggota tim.
     */
    /**
     * @return HasMany<AnggotaTim, $this>
     */
    public function anggotaTims(): HasMany
    {
        return $this->hasMany(AnggotaTim::class);
    }
}
