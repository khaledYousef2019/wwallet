<?php

namespace App\Http\Controllers\Api;

use App\DB\Models\ContactDetails;
use App\DB\Models\PersonalDetails;
use App\DB\Models\Token;
use App\Events\UserRegister;
use App\Helpers\UserHelpers;
use App\Http\Services\AuthService;
use co;
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


class AuthController
{

    protected $service;
    function __construct()
    {
        $this->service = new AuthService();
    }
    public function loginHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {


        $data = $request->getParsedBody();
//        $validator = $this->validateLoginForm(ArrayHelpers::only($data, ['email', 'password']));
        $validator = Validator::make(ArrayHelpers::only($data, ['email', 'password']),[
                'email' => 'required|email|max:100|exist:email,'.User::class,
                'password' => 'required|string|min:8',
//            'password_confirmation' => 'required|string|min:8',
            ]
        );
        if ($validator->getViolations()){
            $response->getBody()->write(json_encode(['success' => false, 'errors' =>$validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !password_verify($data['password'], $user->password)) {
            Events::dispatch(new UserLoginFail($data['email']));
            $response->getBody()->write(json_encode(['error' => 'Failed to authenticate: Invalid email or password']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        Events::dispatch(new UserLogin($user));

        $token = JwtToken::create(
            name: uniqid(),
            userId: $user->id,
            expire: 300,
            useLimit: 20
        )->token;

        $session = $request->getAttribute('session');
        $sessionTable = SessionTable::getInstance();
        $sessionTable->set($session['id'], ['id' => $session['id'], 'user_id' => $user->id]);

        $responseData = [
            'message' => 'Login successful',
            'user_id' => $user->id,
            'token' => $token
        ];

        $response->getBody()->write(json_encode(['data' => $responseData]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws Exception
     */
    public function logoutHandler(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $sessionTable = SessionTable::getInstance();
        $sessionData = $sessionTable->get($session['id']);
        Events::dispatch(new UserLogout(User::find($sessionData['user_id'])));
        $sessionTable->delete($session['id']);
//        Token::deleteToken();
        $responseData = ['message' => 'Logout successful'];
        $response->getBody()->write(json_encode($responseData));

        return $response->withStatus(204)->withHeader('Content-Type', 'application/json');
    }
    public function registerHandler(RequestInterface $request, ResponseInterface $response, $args)
    {
        global $app;

        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['email', 'password','username','password_confirmation']),[
            'username' => 'required|string|max:255|unique:username,'.User::class,
            'email' => 'required|email|max:100|unique:email,'.User::class,
            'password' => 'required|string|min:8',
//            'password_confirmation' => 'required|string|min:8',
            ]
        );
        if ($validator->getViolations()){
            $response->getBody()->write(json_encode(['success' => false, 'errors' =>$validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        try {
            $user = User::create([
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'username' => $data['username'],
                'role' => USER_ROLE_USER
            ]);
//            ContactDetails::create([
//                'user_id' => $user->id,
//                'country' => $data['country'],
//                'city' => $data['city'],
//                'street' => $data['street'],
//                'postal_code' => $data['postal_code'],
//                'phone' => $data['phone'],
//                'country_code' => $data['country_code']
//            ]);
//            PersonalDetails::create([
//                'user_id' => $user->id,
//                'first_name' => $data['first_name'],
//                'last_name' => $data['last_name'],
//                'birth_date' => $data['birthday'],
//                'gender' => $data['gender'],
//                'photo' => $data['photo'],
//            ]);


            Events::dispatch(new UserRegister($user));
            $response->getBody()->write(json_encode(['success' => true,'message' => 'Sign up successful, Please verify your mail']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $app->getContainer()->get('logger')->error('Error In System ' . $e->getMessage());
            $response->getBody()->write(json_encode(['success' => false,'error' => 'Failed to register '. $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function checkEmail(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        global $app;

        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['email', 'password','username','password_confirmation']),[
                'email' => 'required|email|max:100|unique:email,'.User::class,
            ]
        );
        if ($validator->getViolations()){
            $response->getBody()->write(json_encode(['success' => false, 'errors' =>$validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Valid Email']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    public function checkUsername(RequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        global $app;

        $data = $request->getParsedBody();
        $validator = Validator::make(ArrayHelpers::only($data, ['email', 'password','username','password_confirmation']),[
                'username' => 'required|string|min:6|max:255|unique:username,'.User::class,
            ]
        );
        if ($validator->getViolations()){
            $response->getBody()->write(json_encode(['success' => false, 'errors' =>$validator->getViolations()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Valid Username']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
}
