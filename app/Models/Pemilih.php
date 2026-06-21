<?php

namespace App\Models;

use App\Casts\EncryptedAesGcm;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $nik           🔐 AES-256-GCM
 * @property string $nik_hash      SHA-256 (untuk cek duplikat)
 * @property string $nama          🔐 AES-256-GCM
 * @property string $jenis_kelamin 🔐 AES-256-GCM  (L | P)
 * @property string $alamat        🔐 AES-256-GCM
 * @property string $rt            🔐 AES-256-GCM
 * @property string $rw            🔐 AES-256-GCM
 * @property string $desa_id
 * @property string $kecamatan_id
 * @property string $user_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Desa $desa
 * @property-read \App\Models\Kecamatan $kecamatan
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereDesaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereKecamatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereNikHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereRt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereRw($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pemilih whereUserId($value)
 * @mixin \Eloquent
 */
class Pemilih extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'nik',
        'nik_hash',
        'nama',
        'jenis_kelamin',
        'alamat',
        'rt',
        'rw',
        'desa_id',
        'kecamatan_id',
        'user_id',
        'relawan_id',
    ];

    protected function casts(): array
    {
        return [
            'nik'           => EncryptedAesGcm::class, // 🔐 AES-256-GCM
            'nama'          => EncryptedAesGcm::class, // 🔐 AES-256-GCM
            'alamat'        => EncryptedAesGcm::class, // 🔐 AES-256-GCM
            'rt'            => EncryptedAesGcm::class, // 🔐 AES-256-GCM
            'rw'            => EncryptedAesGcm::class, // 🔐 AES-256-GCM
        ];
    }

    // ─── Helper untuk set NIK (otomatis buat hash) ────────────────

    /**
     * Set NIK dan otomatis hitung nik_hash untuk validasi duplikat.
     */
    public function setNikAttribute(string $nik): void
    {
        $this->attributes['nik']      = app(EncryptedAesGcm::class)->set($this, 'nik', $nik, []);
        $this->attributes['nik_hash'] = hash('sha256', $nik);
    }

    // ─── Relasi ───────────────────────────────────────────────────

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relawan(): BelongsTo
    {
        return $this->belongsTo(AnggotaTim::class, 'relawan_id');
    }
}
