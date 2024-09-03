<?php
namespace App\Http\Controllers\Api;

use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use App\DB\Models\Token;
use App\Events\UserRegister;
use App\Helpers\TokenHelper;
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

class AuthController extends BaseController
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
        return $this->jsonResponse($response, $result, $result['status']);

    }

    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $tokenString = TokenHelper::extractToken($request);
            $decoded = TokenHelper::validateToken($tokenString);

            $userId = $decoded['user_id'] ?? null;
            $sessionTable = SessionTable::getInstance();
            $token = Token::where('token', $tokenString)->first();

            if (!$token || !$token->name) {
                throw new Exception('Token not found or invalid');
            }

            // Remove the token
            JwtToken::removeToken($token);

            if ($userId) {
                Events::dispatch(new UserLogout($token));
            }

            return $this->jsonResponse($response, ['message' => 'Logout successful'], 204);
        } catch (Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }


    public function registerHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $validator = Validator::make($data, [
            'username' => 'required|string|max:255|unique:username,' . User::class,
            'email' => 'required|email|max:100|unique:email,' . User::class,
            'password' => 'required|string|strong_password|min:8',
            ]);

        if ($validator->getViolations()) {
            return $this->jsonResponse($response, ['success' => false, 'errors' => $validator->getViolations()], 400);
        }

        try {
            $result = $this->service->signUpProcess($data);
            return $this->jsonResponse($response, ['success' => $result['success'], 'message' => $result['message']], $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            return $this->jsonResponse($response, ['success' => false, 'error' => 'Failed to register: ' . $e->getMessage()], 500);
        }
    }

    public function checkEmail(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $validator = Validator::make($data, [
            'email' => 'required|email|max:100|unique:email,' . User::class,
        ]);

        if ($validator->getViolations()) {
            return $this->jsonResponse($response, ['success' => false, 'errors' => $validator->getViolations()], 400);
        }

        return $this->jsonResponse($response, ['success' => true, 'message' => 'Valid Email'],200);
    }

    public function checkUsername(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $data = $request->getParsedBody();
        $validator = Validator::make($data, [
            'username' => 'required|string|min:6|max:255|unique:username,' . User::class,
        ]);

        if ($validator->getViolations()) {
            return $this->jsonResponse($response, ['success' => false, 'errors' => $validator->getViolations()], 400);
        }

        return $this->jsonResponse($response, ['success' => true, 'message' => 'Valid Username'],200);
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
