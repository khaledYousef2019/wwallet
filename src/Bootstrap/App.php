<?php

namespace App\Bootstrap;

use App\Commands\ScheduleCurrencyFetch;
use App\Events\FetchCurrencyInfo;
use App\Http\Middlewares\NotFoundHandlerMiddleware;
use App\Listeners\FetchCurrencyInfoListener;
use DI\Container;
use Ilex\SwoolePsr7\SwooleServerRequestConverter;
use App\Commands\GenerateFactory;
use App\Commands\GenerateJwtToken;
use App\Commands\HttpServer;
use App\Commands\Migrate;
use App\Commands\Seed;
use App\Commands\WebSocketServer;
use App\Events\EventInterface;
use App\Events\UserLogin;
use App\Events\UserLoginFail;
use App\Events\UserLogout;
use App\Services\Events;
use Nyholm\Psr7\Factory\Psr17Factory;
use Slim\App as SlimApp;
use Slim\Routing\RouteCollectorProxy;
use Symfony\Component\Console\Application;

class App
{
    /**
     * @throws \Exception
     */
    public static function start(): void
    {
        $app = App::prepareSlimApp();

        Dependencies::start($app);
        self::registerEvents($app);
        self::registerRoutes($app);
        self::addNotFoundMiddleware($app);
        self::ConnectionPoolMiddleware($app);
        self::processCommands();
    }

    public static function registerRoutes(SlimApp $app): void
    {
        (require ROOT_DIR . '/src/routes.php')($app);

        $app->group('/api', function(RouteCollectorProxy $group) {
            (require ROOT_DIR . '/src/api-routes.php')($group);
        });
    }

    private static function prepareSlimApp(): SlimApp
    {
        global $app, $requestConverter;

        $psr17Factory = new Psr17Factory;
        $requestConverter = new SwooleServerRequestConverter(
            $psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory
        );
        $app = new SlimApp($psr17Factory, new Container());
        $app->addRoutingMiddleware();
        return $app;
    }

    private static function addNotFoundMiddleware(SlimApp $app): void
    {
        $app->add(NotFoundHandlerMiddleware::class);
    }
    private static function ConnectionPoolMiddleware(SlimApp $app): void
    {
        $app->add(\App\Middlewares\ConnectionPoolMiddleware::class);
    }


    /**
     * @throws \Exception
     */
    private static function processCommands(): void
    {
        $application = new Application();

        $application->add(new HttpServer);
        $application->add(new WebSocketServer);
        $application->add(new Migrate);
        $application->add(new Seed);
        $application->add(new GenerateJwtToken);
        $application->add(new GenerateFactory);
        $application->add(new ScheduleCurrencyFetch());

        $application->run();
    }

    private static function registerEvents(SlimApp $app): void
    {
        $container = $app->getContainer();

        Events::addListener(UserLogin::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('User successful login: ' . $event->user->name);
        });

        Events::addListener(UserLogout::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('User logout: ' . $event->user->name);
        });

        Events::addListener(UserLoginFail::class, function(EventInterface $event) use ($container) {
            $container->get('logger')->info('Login attempt fail: ' . $event->email);
        });
        Events::addListener(FetchCurrencyInfo::class, [new FetchCurrencyInfoListener(), 'handle']);
    }
}