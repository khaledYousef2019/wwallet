<?php


use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Middlewares\AuthorizationMiddleware;
use App\Http\Middlewares\CheckUsersExistenceMiddleware;
use App\Http\Middlewares\SessionMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', HomeController::class . ':welcome');

        $group->group('', function (RouteCollectorProxy $group2) {
            $group2->get('/login', LoginController::class . ':login')->setName('login');
            $group2->post('/login', LoginController::class . ':loginHandler')->setName('login-handler');

            $group2->post('/logout', LoginController::class . ':logoutHandler')->setName('logout-handler');

            $group2->get('/admin', AdminController::class . ':admin')
                ->setName('admin');
        })->add(new AuthorizationMiddleware);

    })->add(new SessionMiddleware);
};