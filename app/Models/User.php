<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $id
 * @property string $name 🔐 AES-256-GCM
 * @property string $email 🔐 AES-256-GCM
 * @property string $email_hash SHA-256 (untuk login lookup)
 * @property string $password 🔑 Argon2id
 * @property string $role admin | kecamatan | desa
 * @property string|null $kecamatan_id
 * @property string|null $desa_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Desa|null $desa
 * @property-read Kecamatan|null $kecamatan
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDesaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKecamatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
            'name' => EncryptedAesGcm::class,  // 🔐 AES-256-GCM
            'email' => EncryptedAesGcm::class,  // 🔐 AES-256-GCM
            'password' => 'hashed',                // 🔑 Argon2id
        ];
    }

    // ─── Relasi ───────────────────────────────────────────────

    /**
     * @return BelongsTo<Kecamatan, $this>
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * @return BelongsTo<Desa, $this>
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * @return HasMany<Pemilih, $this>
     */
    public function pemilihs(): HasMany
    {
        return $this->hasMany(Pemilih::class);
    }
}
