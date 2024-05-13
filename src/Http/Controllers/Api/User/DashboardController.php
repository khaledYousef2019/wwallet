<?php

namespace App\Http\Controllers\Api\User;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DashboardController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $arr = [];
        for($i = 1; $i <= 50000; $i++) {
            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
            $arr[]= ['data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i,'data' => 'test'.$i];
        }
        $response->getBody()->write(json_encode($arr));
        return $response;
    }
}