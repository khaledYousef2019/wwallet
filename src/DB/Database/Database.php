<?php

namespace App\DB\Database;


use OpenSwoole\Core\Coroutine\Client\PDOClientFactory;
use OpenSwoole\Core\Coroutine\Client\PDOConfig;
use OpenSwoole\Core\Coroutine\Pool\ClientPool;

class Database
{
    private static $instance;
    private $pool;

    private function __construct()
    {
        // Load database configuration

        // Initialize the PDO pool
        $this->pool = new ClientPool(PDOClientFactory::class, (new PDOConfig())
            ->withHost($_ENV['DB_HOST'])
            ->withPort($_ENV['DB_PORT'])
            ->withDbName($_ENV['DB_DATABASE'])
            ->withCharset($_ENV['DB_CHARSET'] ?? 'utf8')
            ->withUsername($_ENV['DB_USERNAME'])
            ->withPassword($_ENV['DB_PASSWORD']), 8, true);

    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPool()
    {
        return $this->pool;
    }
}
