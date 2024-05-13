<?php

namespace App\Http\Middlewares;

use Exception;
use App\DB\User;
use App\Services\Session;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandler $handler)
    {
        $request = $request->withAttribute('session', Session::startSession($request));
        $response = $handler->handle($request);
        Session::addCookiesToResponse($request, $response);
        return $response;
    }
}
