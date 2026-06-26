<?php

namespace App\Casts;

use App\Services\AesGcmEncryption;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast otomatis enkripsi/dekripsi field dengan AES-256-GCM.
 * Digunakan di $casts pada model Eloquent.
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class EncryptedAesGcm implements CastsAttributes
{
    /**
     * Dekripsi saat membaca dari database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return $value === '' ? '' : null;
        }

        return AesGcmEncryption::decrypt((string) $value);
    }

    /**
     * Enkripsi saat menyimpan ke database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return $value === '' ? '' : null;
        }

        return AesGcmEncryption::encrypt((string) $value);
    }
}
