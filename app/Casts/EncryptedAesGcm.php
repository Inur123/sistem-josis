<?php

namespace App\Casts;

use App\Services\AesGcmEncryption;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Cast otomatis enkripsi/dekripsi field dengan AES-256-GCM.
 * Digunakan di $casts pada model Eloquent.
 */
class EncryptedAesGcm implements CastsAttributes
{
    /**
     * Dekripsi saat membaca dari database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return AesGcmEncryption::decrypt($value);
    }

    /**
     * Enkripsi saat menyimpan ke database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return AesGcmEncryption::encrypt((string) $value);
    }
}
