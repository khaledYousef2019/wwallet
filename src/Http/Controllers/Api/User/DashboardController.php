<?php

namespace MyCode\Http\Controllers\Api\User;

use MyCode\DB\Models\User;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DashboardController
{
    public function index(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = User::all()->map(function(User $user) {
            $userData = $user->toArray();
            unset($userData['password']);
            unset($userData['created_at']);
            unset($userData['updated_at']);
            return $userData;
        });

        $response->getBody()->write(json_encode(['data' => $data]));
        return $response;
    }
}