<?php

namespace App\Bootstrap;

use Illuminate\Database\Capsule\Manager as DbCapsule;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Slim\App as SlimApp;

class Dependencies
{
    public static function start(SlimApp $app)
    {
        self::registerLogger($app);
        self::registerErrorHandlers();
        self::registerDbCapsule($app);
        self::registerFilesystem($app);
        self::registerJsonResponse();
    }

    private static function registerLogger(SlimApp $app)
    {
        $app->getContainer()->set('logger', function() {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'].'/app.log', Logger::DEBUG));
            return $logger;
        });
    }
    private static function registerJsonResponse(SlimApp $app)
    {
        $app->getContainer()->set('jsonResponse', function () use ($app) {
            return function ($data, int $status = 200, array $headers = []) use ($app) {
                $response = $app->getContainer()->get(ResponseInterface::class);
                $payload = json_encode($data);

                $response->getBody()->write($payload);
                foreach ($headers as $key => $value) {
                    $response = $response->withHeader($key, $value);
                }

                return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
            };
        });
    }
    private static function registerErrorHandlers()
    {
        // Prevent errors from being displayed in the console
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');

        // Set error handler
        set_error_handler(function ($severity, $message, $file, $line) {
            // Handle the error, log it and stop it from reaching the console
            $logger = new Logger('php_errors');
            $logger->pushHandler(new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'].'/error.log', Logger::ERROR));
            $logger->error("Error: [$severity] $message in $file on line $line");
        });

        // Set exception handler
        set_exception_handler(function ($exception) {
            // Handle the exception, log it and stop it from reaching the console
            $logger = new Logger('php_exceptions');
            $logger->pushHandler(new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'].'/exception.log', Logger::ERROR));
            $logger->error("Uncaught exception: " . $exception->getMessage(), [
                'exception' => $exception,
            ]);
        });

        // Prevent fatal errors from being displayed in the console
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== NULL) {
                $logger = new Logger('php_fatal_errors');
                $handler = new StreamHandler(ROOT_DIR . '/' . $_ENV['LOG_STORAGE'].'/fatal_error.log', Logger::ERROR);

                // Format the log output with line breaks and indentation
                $formatter = new \Monolog\Formatter\LineFormatter(
                    "[%datetime%] %channel%.%level_name%:\nMessage: %message%\nContext: %context%\nStack Trace:\n%extra%\n###############################################\n\n\n\n\n\n", // Custom format
                    null,
                    true,
                    true
                );
                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);

                $error_message = sprintf(
                    "Fatal error: %s in %s on line %d\nStack trace:\n%s",
                    $error['message'],
                    $error['file'],
                    $error['line'],
                    (isset($error['trace']) ? $error['trace'] : '[No stack trace available]')
                );

                $logger->error($error_message, [
                    'exception' => $error,
                ]);
            }
        });
    }

    private static function registerDbCapsule(SlimApp $app)
    {
        $container = $app->getContainer();

        $container->set('db', function () {
            $capsule = new DbCapsule;
            $capsule->addConnection([
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
                'collation' => $_ENV['DB_COLLATION'] ?? 'utf8_unicode_ci',
                'prefix' => $_ENV['DB_PREFIX'] ?? '',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            return $capsule;
        });

        // start db
        $container->get('db');
    }

    private static function registerFilesystem(SlimApp $app)
    {
        $app->getContainer()->set('filesystem', function() {
            $adapter = new LocalFilesystemAdapter(ROOT_DIR);
            return new Filesystem($adapter);
        });
    }
}