<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Jakarta'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

// ===== REGISTER CONFIG =====
$app->configure('auth');
$app->configure('database');
$app->configure('jwt'); // 🔥 TAMBAHKAN INI

// ===== REGISTER MIDDLEWARE =====
$app->middleware([
    App\Http\Middleware\CorsMiddleware::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

// ===== REGISTER SERVICE PROVIDERS =====
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);

// ===== REGISTER ALIAS =====
if (!class_exists('JWTAuth')) {
    class_alias(Tymon\JWTAuth\Facades\JWTAuth::class, 'JWTAuth');
}
if (!class_exists('JWTFactory')) {
    class_alias(Tymon\JWTAuth\Facades\JWTFactory::class, 'JWTFactory');
}

// ===== REGISTER ROUTES =====
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../app/Http/Routes/web.php';
});

return $app;