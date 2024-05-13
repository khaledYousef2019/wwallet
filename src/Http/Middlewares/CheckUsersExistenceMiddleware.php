<?php

namespace App\Http\Middlewares;

use Exception;
use App\DB\User;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CheckUsersExistenceMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandler $handler)
    {
        $id = $handler->getArgument('id');
        $user = (new User)->find($id);
        
        if (count($user) === 0) {
            throw new Exception('Record doesn\'t exist');
        }

        $response = $handler->handle($request);
        return $response;
    }
}
