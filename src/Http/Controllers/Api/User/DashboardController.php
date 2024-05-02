<?php

namespace MyCode\Http\Controllers\Api\User;

use MyCode\DB\Models\User;
use MyCode\Services\SessionTable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DashboardController
{
//    public function index(RequestInterface $request, ResponseInterface $response): ResponseInterface
//    {
//        $data = User::all()->map(function(User $user) {
//            $userData = $user->toArray();
//            unset($userData['password']);
//            unset($userData['created_at']);
//            unset($userData['updated_at']);
//            return $userData;
//        });
//
//        $response->getBody()->write(json_encode(['data' => $data]));
//        return $response;
//    }

    public function index(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->getAttribute('session')['id']);
        $user = User::find($session_data['user_id']);
        if ($user){
            unset($user['password']);
            unset($user['created_at']);
            unset($user['updated_at']);

        }

        $response->getBody()->write(json_encode(['data' => $user]));
        return $response;

    }
}