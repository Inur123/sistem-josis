<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property 'korcam'|'kordes'|'relawan' $role
 * @property string|null $kecamatan_id
 * @property string|null $desa_id
 * @property string $nama
 * @property string|null $nik
 * @property string|null $no_hp
 * @property string|null $alamat
 * @property-read Kecamatan|null $kecamatan
 * @property-read Desa|null $desa
 * @property-read Collection<int, Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 */
class AnggotaTim extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory, HasUlids;

    protected $table = 'anggota_tim';

    protected $fillable = [
        'role',
        'kecamatan_id',
        'desa_id',
        'nama',
        'nik',
        'no_hp',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'nama' => EncryptedAesGcm::class,
            'nik' => EncryptedAesGcm::class,
            'no_hp' => EncryptedAesGcm::class,
            'alamat' => EncryptedAesGcm::class,
        ];
    }

    /**
     * Relasi ke kecamatan.
     */
    /**
     * @return BelongsTo<Kecamatan, $this>
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Relasi ke desa.
     */
    /**
     * @return BelongsTo<Desa, $this>
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Relasi ke pemilih (voters).
     */
    /**
     * @return HasMany<Pemilih, $this>
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class, 'relawan_id');
    }
}
