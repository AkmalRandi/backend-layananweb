<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Test ping
$router->get('/ping', function () {
    return response()->json([
        'status' => true,
        'message' => 'Pong! Backend is running',
        'time' => date('Y-m-d H:i:s')
    ]);
});

// Auth
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/logout', 'AuthController@logout');

// User
$router->get('/user/profile', 'UserController@profile');

// Quiz
$router->get('/quizzes', 'QuizController@index');
$router->get('/quizzes/{id}', 'QuizController@show');
$router->post('/quizzes', 'QuizController@store');
$router->put('/quizzes/{id}', 'QuizController@update');
$router->delete('/quizzes/{id}', 'QuizController@destroy');

// Quiz Play
$router->post('/quizzes/{id}/start', 'QuizController@start');
$router->post('/quizzes/{id}/submit', 'QuizController@submit');
$router->get('/quizzes/{id}/result', 'QuizController@result');