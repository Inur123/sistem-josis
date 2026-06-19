<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $id
 * @property string $name           🔐 AES-256-GCM
 * @property string $email          🔐 AES-256-GCM
 * @property string $email_hash     SHA-256 (untuk login lookup)
 * @property string $password       🔑 Argon2id
 * @property string $role           admin | kecamatan | desa
 * @property string|null $kecamatan_id
 * @property string|null $desa_id
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, HasUlids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'password',
        'role',
        'kecamatan_id',
        'desa_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'name'     => EncryptedAesGcm::class,  // 🔐 AES-256-GCM
            'email'    => EncryptedAesGcm::class,  // 🔐 AES-256-GCM
            'password' => 'hashed',                // 🔑 Argon2id
        ];
    }

    // ─── Relasi ───────────────────────────────────────────────

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class);
    }
}
