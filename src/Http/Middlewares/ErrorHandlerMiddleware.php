<?php

namespace App\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            if (!$response instanceof ResponseInterface) {
                // Ensure response is valid
                return $this->createErrorResponse("Invalid response type");
            }
            return $response;
        } catch (\Throwable $e) {
            // Log the error and return a proper response
            $this->logger->error($e->getMessage());
            return $this->createErrorResponse("An error occurred");
        }
    }

    private function createErrorResponse(string $message): ResponseInterface
    {
        $responseFactory = new Psr17Factory();
        $response = $responseFactory->createResponse(500);
        $response->getBody()->write($message);
        return $response;
    }
}
