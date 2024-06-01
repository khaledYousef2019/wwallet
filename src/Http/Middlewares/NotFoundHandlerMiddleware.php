<?php

namespace App\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use Slim\Exception\HttpNotFoundException;

class NotFoundHandlerMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Resource not found',
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}
