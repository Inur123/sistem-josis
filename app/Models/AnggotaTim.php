<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnggotaTim extends Model
{
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
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Relasi ke desa.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * Relasi ke pemilih (voters).
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class, 'relawan_id');
    }
}
