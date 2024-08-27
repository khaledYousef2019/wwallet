<?php

namespace App\Http\Controllers\Api;


use Psr\Http\Message\ResponseInterface;

abstract class BaseController
{
//    private $response;
//
//
//    protected function getResponse(): ResponseInterface
//    {
//        return $this->response;
//    }
//
//    protected function jsonResponse($data, int $status = 200, array $headers = []): ResponseInterface
//    {
////        $response = $this->getResponse();
//        $payload = json_encode($data);
//
//        $response->getBody()->write($payload);
//        foreach ($headers as $key => $value) {
//            $response = $response->withHeader($key, $value);
//        }
//
//        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
//    }

//    abstract protected function getResponse(): ResponseInterface;


    protected function jsonResponse(ResponseInterface $response, $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

}