<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $kode
 * @property string $nama
 * @property string $kecamatan_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Kecamatan $kecamatan
 * @property-read Collection<int, Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereKecamatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereUpdatedAt($value)
 *
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

    /**
     * Relasi ke anggota tim.
     */
    public function anggotaTims(): HasMany
    {
        return $this->hasMany(AnggotaTim::class);
    }
}
