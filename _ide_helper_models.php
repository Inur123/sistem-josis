<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @mixin \Eloquent
 * @property string $id
 * @property string $kode
 * @property string $nama
 * @property string $kecamatan_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Kecamatan $kecamatan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereKecamatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Desa whereUpdatedAt($value)
 */
	class Desa extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin \Eloquent
 * @property string $id
 * @property string $kode
 * @property string $nama
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Desa> $desas
 * @property-read int|null $desas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pemilih> $pemilihs
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
 */
	class Kecamatan extends \Eloquent {}
}

namespace App\Models{
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
 * @mixin \Eloquent
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
 */
	class Pemilih extends \Eloquent {}
}

namespace App\Models{
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
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Desa|null $desa
 * @property-read \App\Models\Kecamatan|null $kecamatan
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pemilih> $pemilihs
 * @property-read int|null $pemilihs_count
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
 */
	class User extends \Eloquent {}
}

