<?php


use MyCode\Http\Controllers\AdminController;
use MyCode\Http\Controllers\Api\User\DashboardController;
use MyCode\Http\Controllers\AuthController;
use MyCode\Http\Controllers\Api\AuthController as ApiAuthController;
use MyCode\Http\Controllers\HomeController;
use MyCode\Http\Controllers\LoginController;
use MyCode\Http\Middlewares\AuthorizationMiddleware;
use MyCode\Http\Middlewares\CheckUsersExistenceMiddleware;
use MyCode\Http\Middlewares\SessionMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', HomeController::class . ':welcome');

        $group->group('', function (RouteCollectorProxy $group2) {
            $group2->get('/login', AuthController::class . ':login')->setName('login');
            $group2->post('/login', AuthController::class . ':loginHandler')->setName('login-handler');

            $group2->post('/logout', AuthController::class . ':logoutHandler')->setName('logout-handler');

            $group2->get('/admin', AdminController::class . ':admin')
                ->setName('admin');
        })->add(new AuthorizationMiddleware);

    })->add(new SessionMiddleware);

    $app->group('/api/', function (RouteCollectorProxy $group) {
        $group->post('login', ApiAuthController::class . ':loginHandler')->setName('login-handler');

        $group->get('user-dashboard-app', DashboardController::class . ':userDashboardApp');


    })->add(new SessionMiddleware);




};