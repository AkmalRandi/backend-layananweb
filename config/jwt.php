<?php

return [
    'secret' => env('JWT_SECRET'),
    'algo' => 'HS256',
    'ttl' => env('JWT_TTL', 60),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    'leeway' => env('JWT_LEEWAY', 0),
    'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
    'persistent_claims' => [],
    'lock_subject' => true,
    'leeway' => 0,
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
    'decrypt_cookies' => false,
    'providers' => [
        'jwt' => Tymon\JWTAuth\Providers\JWT\Lcobucci::class,
        'auth' => Tymon\JWTAuth\Providers\Auth\Illuminate::class,
        'storage' => Tymon\JWTAuth\Providers\Storage\Illuminate::class,
    ],
];