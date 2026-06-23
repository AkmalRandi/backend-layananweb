notepad app\Http\Routes\web.php
<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// ===== TEST ROUTE =====
$router->get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Pong! API is working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// ===== AUTH ROUTES =====
$router->post('/auth/register', 'AuthController@register');
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/logout', 'AuthController@logout');
$router->get('/user/profile', 'AuthController@profile');
$router->post('/auth/refresh', 'AuthController@refresh');

// ===== PROTECTED ROUTES =====
$router->group(['middleware' => 'auth'], function () use ($router) {
    
    // TEACHER ROUTES
    $router->group(['prefix' => 'teacher'], function () use ($router) {
        $router->get('/quizzes', 'QuizController@getTeacherQuizzes');
    });

    // QUIZ ROUTES
    $router->post('/quizzes', 'QuizController@createQuiz');
    $router->delete('/quizzes/{id}', 'QuizController@deleteQuiz');
    $router->patch('/quizzes/{id}/visibility', 'QuizController@toggleVisibility');
    $router->get('/quizzes/{id}/results', 'QuizController@getQuizResults');

    // STUDENT ROUTES
    $router->get('/quizzes', 'QuizController@getStudentQuizzes');
    $router->get('/quizzes/{id}', 'QuizController@getQuizDetail');
    $router->post('/quizzes/join/{joinCode}', 'QuizController@joinQuiz');
    $router->post('/quizzes/{id}/start', 'QuizController@startQuiz');
    $router->post('/quizzes/{id}/submit', 'QuizController@submitQuiz');
    $router->get('/quizzes/{id}/result', 'QuizController@getResult');
});