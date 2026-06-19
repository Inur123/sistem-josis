<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Sistem JOSIS menggunakan Argon2id — standar OWASP 2024 untuk password hashing.
    | Argon2id tahan terhadap GPU attack dan side-channel attack.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options (tidak digunakan, tapi dipertahankan sebagai fallback)
    |--------------------------------------------------------------------------
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('HASH_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | memory   : memory cost dalam KB (64MB = 65536)
    | threads  : jumlah thread paralel
    | time     : time cost (iterasi)
    |
    | Nilai berikut sesuai rekomendasi OWASP untuk Argon2id:
    |
    */

    'argon' => [
        'memory'  => env('ARGON_MEMORY', 65536),   // 64 MB
        'threads' => env('ARGON_THREADS', 1),
        'time'    => env('ARGON_TIME', 2),
        'verify'  => env('HASH_VERIFY', true),
    ],

];
