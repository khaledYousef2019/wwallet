<?php

namespace App\Services;

use App\Events\TokenChanged;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\DB\Models\Token;
use Psr\Http\Message\ServerRequestInterface as Request;

class JwtToken
{
    const HS256_ALGORITHM = 'HS256';
    const TOKEN_NAME = 'your_token_name'; // Define a constant for the token name

    /**
     * @param string $token
     * @param string $name
     * @return array
     * @throws Exception
     */
    public static function decodeJwtToken(string $token, string $name): array
    {
        try {
            $decoded = JWT::decode($token, new Key($name, self::HS256_ALGORITHM));
            return (array) $decoded;
        } catch (Exception $e) {
            throw new Exception('Token decoding failed: ' . $e->getMessage());
        }
    }

    public static function getToken(Request $request): ?Token
    {
        global $app;

        if (!$request->hasHeader('Authorization')) {
            return null;
        }

        $authorization = $request->getHeader('Authorization');
        $authorization = current($authorization);
        $authorization = explode(' ', $authorization);

        if ($authorization[0] !== 'Bearer') {
            return null;
        }

        $token = $authorization[1];

        try {
            $tokenRecord = Token::where('token', $token)->first();
            if (!$tokenRecord) {
                $app->getContainer()->get('logger')->error('Token not found');
                return null;
            }
//
//            // Optionally, you could remove the token immediately after use
//            $tokenRecord->delete();

            return $tokenRecord;
        } catch (Exception $e) {
            $app->getContainer()->get('logger')->error('Invalid token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param string $name Token's name.
     * @param int $userId User to attach to token.
     * @param int $expire Seconds to expire.
     * @param ?int $useLimit Uses limit number for token. Null for no limit.
     * @return Token
     */
    public static function create(string $name, int $userId, ?int $expire, ?int $useLimit = null): Token
    {
        if (null !== $expire) {
            $expire = Carbon::now()->addMinutes($expire);
        }

        $payload = [
            "iat" => Carbon::now()->timestamp,
            "user_id" => $userId,
        ];

        $tokenData = [
            'name' => $name,
            'user_id' => $userId,
            'expire_at' => null,
        ];

        if (null !== $expire) {
            $payload["exp"] = $expire->timestamp;
            $tokenData['expire_at'] = $expire->format('Y-m-d H:i:s');
        }

        $tokenData['token'] = JWT::encode($payload, $name, JwtToken::HS256_ALGORITHM);

        if (null !== $useLimit) {
            $tokenData['use_limit'] = $useLimit;
        }
        $token = Token::create($tokenData);
        Events::dispatch(new TokenChanged($token, 'created'));

        return $token;
    }

    /**
     * Remove a token from the database.
     *
     * @param string $token
     * @return void
     */
    public static function removeToken(string $token): void
    {
        $tokenRecord = Token::where('token', $token)->first();
        if ($tokenRecord) {
            // Dispatch TokenChanged event
            Events::dispatch(new TokenChanged($tokenRecord, 'deleted'));
            $tokenRecord->delete();
        }
    }
}
