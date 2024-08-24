<?php

namespace App\Http\Controllers\Api\User;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Database\PDOPool;

class DashboardController
{
    private $pool;

    public function __construct(PDOPool $pool)
    {
        $this->pool = $pool;
    }
    public function index()
    {
        $pdo = $this->pool->get();

        // Example query
        $statement = $pdo->prepare('SELECT * FROM users');
        $statement->execute();
        $result = $statement->fetchAll();
//        $arr = [];
//        for($i = 1; $i <= 50000; $i++) {
//            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
//            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
//            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
//            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
//            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
//        }
        // Release the connection back to the pool
        $this->pool->put($pdo);

        // Output the result
        echo json_encode($result);
//        $response->getBody()->write(json_encode($result));
//        return $response;
    }
}