<?php

namespace App\Http\Services;

use App\DB\Models\Token;
use App\DB\Models\User;
use App\Events\UserLogin;
use App\Events\UserLoginFail;
use App\Services\Events;
use App\Services\JwtToken;
use App\Services\LoginAttemptsTable;
use App\Services\SessionTable;
use App\Services\Validator;
use Carbon\Carbon;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;

class AuthService
{
    private LoginAttemptsTable $loginAttemptsTable;
    private SessionTable $sessionTable;

    public function __construct()
    {
        $this->loginAttemptsTable = LoginAttemptsTable::getInstance();
        $this->sessionTable = SessionTable::getInstance();
    }

    public function handleLogin(array $data, string $ipAddress, string $device): array
    {
        // Rate limiting: 5 attempts per minute
        $rateLimitKey = 'login_attempt_' . strtolower(trim($data['email'])) . '_' . $ipAddress;
        $attempts = $this->loginAttemptsTable->get($rateLimitKey) ?? 0;

        if ($attempts >= 5) {
            return ['status' => 429, 'message' => 'Too many login attempts. Please try again later.'];
        }

        // Validate login form
        $validator = Validator::make($data, [
            'email' => 'required|email|max:100|exists:email,' . User::class,
            'password' => 'required|string|min:8',
        ]);

        if ($validator->getViolations()) {
            $this->loginAttemptsTable->incr($rateLimitKey, 1, 60); // Increment attempts
            return ['status' => 400, 'message' => 'Validation failed', 'errors' => $validator->getViolations()];
        }

        // Find the user
        $user = User::where('email', $data['email'])->first();

        // Check credentials
        if (!$user || !password_verify($data['password'], $user->password)) {
            Events::dispatch(new UserLoginFail($data['email']));
            $this->loginAttemptsTable->incr($rateLimitKey, 1, 60); // Increment attempts
            return ['status' => 401, 'message' => 'Failed to authenticate: Invalid email or password'];
        }

        // Check for existing token with same IP address and device
        $existingToken = Token::where('user_id', $user->id)
            ->where('ip', $ipAddress)
            ->where('device', $device)
            ->first();

        if ($existingToken && $this->sessionTable->get($existingToken->token)) {
            return [
                'status' => 200,
                'message' => 'You are already logged in',
                'data' => [
                    'user_id' => $user->id,
                    'token' => $existingToken->token
                ]
            ];
        }

        // Destroy any existing token with a different IP address
//        Token::where('user_id', $user->id)
//            ->where('ip', '!=', $ipAddress)
//            ->delete();

        Events::dispatch(new UserLogin($user));

        // Create a new JWT token
        $token = JwtToken::create(
            name: uniqid(),
            userId: $user->id,
            ip: $ipAddress,
            device: $device,
        )->token;

        $this->loginAttemptsTable->reset($rateLimitKey); // Reset attempts on successful login

        return [
            'status' => 200,
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user->id,
                'token' => $token
            ]
        ];
    }

    public function signUpProcess(array $request): array
    {
        $data = ['success' => false, 'data' => [], 'message' => 'Something went wrong'];

        DB::beginTransaction();

        try {
            $mailKey = $this->generateEmailVerificationKey();
            $user = User::create([
                'username' => $request['first_name'],
                'email' => $request['email'],
                'role' => USER_ROLE_USER,
                'password' => Hash::make($request['password']),
            ]);

            DB::commit();

            if ($user) {
                $data = ['success' => true, 'data' => [], 'message' => 'Sign up successful, Please verify your mail'];
            } else {
                $data = ['success' => false, 'data' => [], 'message' => 'Sign up failed'];
            }
        } catch (\Exception $e) {
            DB::rollback();
            if (isset($this->logger)) {
                $this->logger->log('signUpProcess', $e->getMessage());
            }
            $data = ['success' => false, 'data' => [], 'message' => 'Something went wrong'];
        }

        return $data;
    }

    private function generateEmailVerificationKey(): string
    {
        // Generates a random numeric key, consider using a more robust method if needed
        return rand(100000, 999999);
    }


    private function sendVerifyEmail(User $user, string $key): void
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();                                         // Send using SMTP
            $mail->Host       = 'smtp.example.com';                  // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                // Enable SMTP authentication
            $mail->Username   = 'your-email@example.com';            // SMTP username
            $mail->Password   = 'your-email-password';               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 587;                                 // TCP port to connect to

            // Recipients
            $mail->setFrom('no-reply@example.com', 'Your App Name');
            $mail->addAddress($user->email, $user->first_name . ' ' . $user->last_name);

            // Content
            $mail->isHTML(true);                                    // Set email format to HTML
            $mail->Subject = 'Verify Your Email Address';
            $mail->Body    = $this->generateVerificationEmailBody($user, $key);
            $mail->AltBody = strip_tags($mail->Body);

            $mail->send();
        } catch (Exception $e) {
            // Log or handle the error as needed
            if (isset($this->logger)) {
                $this->logger->log('sendVerifyEmail', $e->getMessage());
            }
        }
    }

    private function generateVerificationEmailBody(User $user, string $key): string
    {
        $verificationUrl = "http://example.com/verify-email?key=$key&email=" . urlencode($user->email);

        return <<<HTML
        <html>
        <body>
            <p>Hi {$user->first_name},</p>
            <p>Thank you for registering with us. Please verify your email address by clicking the link below:</p>
            <p><a href="$verificationUrl">Verify Email</a></p>
            <p>If you did not create an account, no further action is required.</p>
            <p>Best regards,<br>Your App Name</p>
        </body>
        </html>
        HTML;
    }
}
