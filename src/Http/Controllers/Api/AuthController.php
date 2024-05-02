<?php

namespace MyCode\Http\Controllers\Api;

use App\Http\Services\AuthService;
use Exception;
use MyCode\DB\Models\User;
use MyCode\Events\UserLogin;
use MyCode\Events\UserLoginFail;
use MyCode\Helpers\ArrayHelpers;
use MyCode\Rules\RecordExist;
use MyCode\Services\Events;
use MyCode\Services\JwtToken;
use MyCode\Services\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class AuthController
{
//    protected $service;
//    function __construct()
//    {
//        $this->service = new AuthService();
//    }

    public function loginHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $data = $request->getParsedBody();
//         $response->getBody()->write(json_encode(['data' => $data]));
//         return $response;
        try {
            /** @throws Exception */
            $this->validateLoginForm(ArrayHelpers::only($data, ['email', 'password']));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => 'Failed to authenticate: ' . $e->getMessage()]));
            return $response;
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !password_verify($data['password'], $user->password)) {
            Events::dispatch(new UserLoginFail($data['email']));
            $response->getBody()->write(json_encode(['error' => 'Failed to authenticate: Invalid email or password']));
            return $response;
//            return $response
//                ->withJson(['error' => 'Failed to authenticate: Invalid email or password'], 401);
        }

        Events::dispatch(new UserLogin($user));

        // You may include additional data in the response if needed
        $responseData = [
            'message' => 'Login successful',
            'user_id' => $user->id,
            'token' => JwtToken::create(
                name: uniqid(),
                userId: $user->id,
                expire: null,
                useLimit: 1
            )->token
        ];
        $response->getBody()->write(json_encode(['data' => $responseData]));
        return $response;
    }

    private function validateLoginForm(array $data): void
    {
        /** @throws Exception */
        Validator::validate($data, [
            'email' => [
                new NotBlank(null, 'User email is required!'),
                new Type('string', 'User email must be a string!'),
                new Email(null, 'User email must be a valid email!'),
                new RecordExist(
                    [
                        'model' => User::class,
                        'field' => 'email',
                    ],
                    'Email is not registered!'
                ),
            ],
            'password' => [
                new NotBlank(null, 'Password is required!'),
                new Type('string', 'Password must be a string!'),
            ],
        ]);
    }

//    function generateJwtToken($userId) {
//        $payload = [
//            'user_id' => $userId,
//            'exp' => time() + 3600 // Token expires in 1 hour
//        ];
//        return \Firebase\JWT\JWT::encode($payload, $yourSecretKey);
//    }

}