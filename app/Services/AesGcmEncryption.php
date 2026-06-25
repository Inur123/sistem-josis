<?php

namespace App\Services;

use RuntimeException;

/**
 * Service enkripsi AES-256-GCM (AEAD)
 *
 * Menggunakan Authenticated Encryption with Associated Data (AEAD) —
 * lebih aman dari AES-256-CBC karena menyertakan authentication tag
 * yang mendeteksi manipulasi data secara otomatis.
 *
 * Format penyimpanan: base64( IV[12] + TAG[16] + CIPHERTEXT )
 */
class AesGcmEncryption
{
    private const ALGORITHM = 'aes-256-gcm';

    private const IV_LENGTH = 12;  // 96-bit nonce (recommended untuk GCM)

    private const TAG_LENGTH = 16;  // 128-bit authentication tag

    /**
     * Enkripsi teks dengan AES-256-GCM.
     */
    public static function encrypt(string $plaintext): string
    {
        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH); // Nonce unik setiap enkripsi
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Enkripsi AES-256-GCM gagal: '.openssl_error_string());
        }

        // Gabungkan IV + TAG + CIPHERTEXT lalu encode ke base64
        return base64_encode($iv.$tag.$ciphertext);
    }

    /**
     * Dekripsi nilai yang dienkripsi AES-256-GCM.
     * Jika data dimanipulasi, authentication tag akan gagal verifikasi.
     */
    public static function decrypt(string $encrypted): string
    {
        $key = self::getKey();
        $data = base64_decode($encrypted, strict: false);

        $minLength = self::IV_LENGTH + self::TAG_LENGTH;

        if (strlen($data) <= $minLength) {
            throw new RuntimeException('Format data enkripsi tidak valid.');
        }

        $iv = substr($data, 0, self::IV_LENGTH);
        $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($data, $minLength);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            // Authentication tag gagal → data kemungkinan telah dimanipulasi
            throw new RuntimeException('Dekripsi gagal — data mungkin telah dimanipulasi di database.');
        }

        return $plaintext;
    }

    /**
     * Derive 32-byte key dari ENCRYPT_KEY di .env
     */
    private static function getKey(): string
    {
        $rawKey = env('ENCRYPT_KEY');

        if (empty($rawKey)) {
            throw new RuntimeException('ENCRYPT_KEY belum diset di .env. Jalankan: php -r "echo base64_encode(random_bytes(32));"');
        }

        // Jika key berbasis base64, decode dulu, lalu derive 32 bytes
        $decoded = base64_decode($rawKey, strict: false);
        $source = ($decoded !== false && strlen($decoded) >= 16) ? $decoded : $rawKey;

        return hash('sha256', $source, true); // Selalu 32 bytes (256-bit)
    }
}
