<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// Ketika mengakses halaman utama (http://localhost:8000/)
$router->get('/', function () use ($router) {
    return "Hello World dari Backend Akmal RS!";
});

// Atau jika ingin format JSON (standar API Backend)
$router->get('/api/hello', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Hello World dari API Backend!'
    ]);
});