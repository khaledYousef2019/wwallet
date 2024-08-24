<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\User\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middlewares\AuthorizationMiddleware;
use App\Http\Middlewares\JwtAuthMiddleware;
use App\Http\Middlewares\SessionMiddleware;
use Slim\Routing\RouteCollectorProxy;

return function (RouteCollectorProxy $group) {
    // Group for protected routes (requiring JWT authentication)
    $group->group('', function (RouteCollectorProxy $group2) {
//        $group2->get('/users', UserController::class . ':index')->setName('api-users-get');
//        $group2->post('/users', UserController::class . ':create')->setName('api-users-create');
//        $group2->put('/users/{user_id}', UserController::class . ':update')->setName('api-users-update');
//        $group2->delete('/users/{user_id}', UserController::class . ':delete')->setName('api-users-delete');

        // Dashboard route requires JWT authentication
        $group2->get('/dashboard', DashboardController::class . ':index')
            ->setName('api-dashboard-get');
    });

    // Group for routes requiring authorization (login and logout)
    $group->group('', function (RouteCollectorProxy $group) {
        $group->post('/register', AuthController::class . ':registerHandler')->setName('register-handler');
        $group->post('/login', AuthController::class . ':loginHandler')->setName('login-handler');
        $group->post('/logout', AuthController::class . ':logoutHandler')->setName('logout-handler');

    })->add(new AuthorizationMiddleware)->add(new SessionMiddleware);

    $group->post('/check-valid-email', AuthController::class . ':checkEmail')->setName('check-email');
    $group->post('/check-valid-username', AuthController::class . ':checkUsername')->setName('check-username');

};
