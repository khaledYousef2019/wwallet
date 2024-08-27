<?php
namespace App\Http\Controllers\Api;

use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use App\DB\Models\Token;
use App\Events\UserRegister;
use App\Helpers\UserHelpers;
use App\Http\Services\AuthService;
use App\Services\LoginAttemptsTable;
use Carbon\Carbon;
use Exception;
use App\DB\Models\User;
use App\Events\UserLogin;
use App\Events\UserLoginFail;
use App\Events\UserLogout;
use App\Helpers\ArrayHelpers;
use App\Services\Events;
use App\Services\JwtToken;
use App\Services\SessionTable;
use App\Services\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Openswoole\Coroutine;
use Symfony\Component\Console\Helper\Table;

class AuthController
{
    protected $service;
    protected $rateLimitKeyPrefix = 'login_attempts_';

    public function __construct()
    {
        $this->service = new AuthService();
    }

    public function loginHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'];
        $device = $request->getHeader('User-Agent')[0] ?? 'unknown';

        $result = $this->service->handleLogin($data, $ipAddress, $device);

        $response->getBody()->write(json_encode($result));
        return $response->withStatus($result['status'])->withHeader('Content-Type', 'application/json');
    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $authorization = $request->getHeader('Authorization');
        if (!$authorization) {
            $response->getBody()->write(json_encode(['error' => 'Authorization header missing']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $authorization = current($authorization);
        $authorization = explode(' ', $authorization);

        if ($authorization[0] !== 'Bearer' || !isset($authorization[1])) {
            $response->getBody()->write(json_encode(['error' => 'Invalid Authorization header']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $tokenString = $authorization[1];
        $sessionTable = SessionTable::getInstance();

        try {
            $token = Token::where('token', $tokenString)->first();
            if (!$token || !$token->name) {
                throw new \Exception('Token not found or invalid');
            }

            $decoded = JwtToken::decodeJwtToken($token->token, $token->name);
            $userId = $decoded['user_id'] ?? null;

            // Remove the token
            JwtToken::removeToken($token);

            if ($userId) {
                Events::dispatch(new UserLogout($token));
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $responseData = ['message' => 'Logout successful'];
        $response->getBody()->write(json_encode($responseData));

        return $response->withStatus(204)->withHeader('Content-Type', 'application/json');
    }

    public function registerHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['email', 'password', 'username', 'password_confirmation']), [
            'username' => 'required|string|max:255|unique:username,' . User::class,
            'email' => 'required|email|max:100|unique:email,' . User::class,
            'password' => 'required|string|min:8',
        ]);

        if ($validator->getViolations()) {
            $response->getBody()->write(json_encode(['success' => false, 'errors' => $validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = $this->service->signUpProcess($data);

            if ($result['success']) {
                $response->getBody()->write(json_encode(['success' => true, 'message' => $result['message']]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['success' => false, 'message' => $result['message']]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Failed to register: ' . $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function checkEmail(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        global $app;

        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['email']), [
            'email' => 'required|email|max:100|unique:email,' . User::class,
        ]);

        if ($validator->getViolations()) {
            $response->getBody()->write(json_encode(['success' => false, 'errors' => $validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Valid Email']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function checkUsername(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        global $app;

        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['username']), [
            'username' => 'required|string|min:6|max:255|unique:username,' . User::class,
        ]);

        if ($validator->getViolations()) {
            $response->getBody()->write(json_encode(['success' => false, 'errors' => $validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Valid Username']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function resendOtpHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        // Logic to handle resending OTP
    }

    public function verifyOtpHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        // Logic to handle OTP verification
    }

    public function forgotPasswordHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        // Logic to handle forgot password
    }
}
