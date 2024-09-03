<?php

namespace App\Helpers;

use Exception;
use App\Services\JwtToken;

class TokenHelper
{
    public static function extractToken($request)
    {
        $authorization = $request->getHeader('Authorization');
        if (!$authorization) {
            throw new Exception('Authorization header missing');
        }

        $authorization = current($authorization);
        $authorization = explode(' ', $authorization);

        if ($authorization[0] !== 'Bearer' || !isset($authorization[1])) {
            throw new Exception('Invalid Authorization header');
        }

        return $authorization[1];
    }

    public static function validateToken($token)
    {
        $jwtToken = JwtToken::getToken();
        if (!$jwtToken || !isset($jwtToken->name)) {
            throw new Exception('Invalid token or token name missing');
        }

        return JwtToken::decodeJwtToken($token, $jwtToken->name);
    }
}
