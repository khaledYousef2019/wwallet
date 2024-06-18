<?php

use OpenSwoole\Core\Coroutine\Client\PDOClientFactory;
use OpenSwoole\Core\Coroutine\Client\PDOConfig;
use OpenSwoole\Core\Coroutine\Pool\ClientPool;
use Swoole\Database\PDOPool;
use Swoole\Runtime;

require_once __DIR__ . '/vendor/autoload.php';

// Ensure OpenSwoole Runtime and Coroutine are enabled
Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

// Define database connection settings
$pdoConfig = new PDOConfig([
    'dsn' => 'mysql:host=localhost;dbname=wwallet;charset=utf8mb4',
    'username' => 'khaled',
    'password' => 'MNIkmy@2018',
]);

// Create a PDO pool
$pool = new ClientPool(PDOClientFactory::class, $pdoConfig, 8, true);

// Coroutine to execute database queries
co::run(function () use ($pool) {
    try {
        // Acquire a connection from the pool
        $pdo = $pool->get();

        // Execute a simple query
        $stmt = $pdo->query('SELECT * FROM users LIMIT 1');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo "PDO is enabled and working with OpenSwoole's connection pool.\n";
            print_r($result);
        } else {
            echo "Query executed but no results returned.\n";
        }

        // Release the connection back to the pool
        $pool->put($pdo);
    } catch (PDOException $e) {
        echo 'PDOException: ' . $e->getMessage() . "\n";
    } catch (Throwable $e) {
        echo 'Exception: ' . $e->getMessage() . "\n";
    }
});
