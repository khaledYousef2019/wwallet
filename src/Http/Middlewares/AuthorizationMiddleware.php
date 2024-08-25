<?php

namespace App\Http\Middlewares;

use App\Services\JwtToken;
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

        if ($is_login_route) {
            // Allow access to login routes without a token
            return $handler->handle($request);
        }

        // Check for Authorization header
        $authorization = $request->getHeader('Authorization');
        if (!$authorization) {
            return $this->unauthorizedResponse('Authorization header missing');
        }

        $authorization = current($authorization);
        $authorization = explode(' ', $authorization);

        if ($authorization[0] !== 'Bearer' || !isset($authorization[1])) {
            return $this->unauthorizedResponse('Invalid Authorization header');
        }

        $token = $authorization[1];

        try {
            // Decode the token to get user information
            $decoded = JwtToken::decodeJwtToken($token, JwtToken::getToken($request)->name);
            $userId = $decoded['user_id'] ?? null;

            if (!$userId) {
                return $this->unauthorizedResponse('Invalid token');
            }

            // Validate the token against the session table
            $session_table = SessionTable::getInstance();
            $session_data = $session_table->get($token);

            if (!$session_data || $session_data['user_id'] !== $userId) {
                return $this->unauthorizedResponse('Token session mismatch');
            }

            // Proceed with the request
            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Failed to decode token: ' . $e->getMessage());
        }
    }

    private function unauthorizedResponse(string $message): Response
    {
        $factory = new Psr17Factory();
        $responseBody = $factory->createStream(json_encode([
            'status' => 'unauthorized',
            'message' => $message,
        ]));

        return (new Response())->withBody($responseBody)->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
