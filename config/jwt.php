<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | Kunci ini digunakan untuk menandatangani token Anda. Perintah
    | `php artisan jwt:secret` akan otomatis mengisi nilai ini di file .env
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Keys
    |--------------------------------------------------------------------------
    |
    | Jika Anda menggunakan algoritma asimetris (seperti RS256), Anda harus
    | mengonfigurasi jalur kunci publik dan privat di sini.
    |
    */

    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'),
        'private' => env('JWT_PRIVATE_KEY'),
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Ttl (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Durasi (dalam menit) token akan berlaku sebelum kedaluwarsa.
    | Defaultnya adalah 1 jam (60 menit).
    |
    */

    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Refresh Ttl
    |--------------------------------------------------------------------------
    |
    | Durasi (dalam menit) token yang kedaluwarsa dapat diperbarui (refresh).
    | Defaultnya adalah 2 minggu.
    |
    */

    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | JWT Algo
    |--------------------------------------------------------------------------
    |
    | Algoritma enkripsi yang digunakan untuk menandatangani token.
    |
    */

    'algo' => env('JWT_ALGO', Tymon\JWTAuth\Providers\JWT\Namshi::class),

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    |
    | Klaim (data) wajib yang harus ada di dalam token JWT Anda.
    |
    */

    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | Persistent Sign-In
    |--------------------------------------------------------------------------
    |
    | Jika disetel ke true, menyegarkan token akan mempertahankan klaim 'sub'
    | yang sama (ID pengguna) dalam token baru.
    |
    */

    'persistent_sign_in' => false,

    /*
    |--------------------------------------------------------------------------
    | Lock Subject
    |--------------------------------------------------------------------------
    |
    | Memastikan bahwa token hanya dapat diverifikasi jika klaim sub cocok
    | dengan model pengguna.
    |
    */

    'lock_subject' => true,

    /*
    |--------------------------------------------------------------------------
    | Leeway
    |--------------------------------------------------------------------------
    |
    | Memberikan toleransi waktu (dalam detik) jika ada perbedaan waktu
    | jam antar server backend dengan client.
    |
    */

    'leeway' => env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Enabled
    |--------------------------------------------------------------------------
    |
    | Jika disetel ke true, token lama akan dimasukkan ke blacklist saat Anda
    | melakukan logout atau refresh token agar tidak bisa dipakai lagi.
    |
    */

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Grace Period
    |--------------------------------------------------------------------------
    |
    | Memberikan waktu toleransi (dalam detik) agar token lama masih bisa dipakai
    | sesaat setelah proses refresh (mencegah error pada request konkuren).
    |
    */

    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Class internal yang digunakan package untuk menangani token,
    | penyimpanan blacklist (cache), dan request data.
    |
    */

    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Namshi::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],

];