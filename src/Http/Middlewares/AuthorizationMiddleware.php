<?php

namespace App\Http\Middlewares;

use App\Services\SessionTable;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class AuthorizationMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandler $handler)
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $is_login_route = in_array($route->getName(), ['login', 'login-handler']);

        $session_table = SessionTable::getInstance();
        $session_data = $session_table->get($request->getAttribute('session')['id']);

        if (!$is_login_route && !isset($session_data['user_id'])) {
            $factory = new Psr17Factory();
            $steam = $factory->createStream(json_encode([
                'status' => 'unauthorized',
                'message' => 'Unauthorized Procedure!',
            ]));
            return (new Response)->withBody($steam)->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if ($is_login_route && isset($session_data['user_id'])) {
            $psr17Factory = new Psr17Factory();
            $responseBody = $psr17Factory->createStream(json_encode(['error' => 'Already Logged In']));
            return (new Response())->withBody($responseBody)->withHeader('Content-Type', 'application/json')->withStatus(303);
        }

        return $handler->handle($request);
    }
}
