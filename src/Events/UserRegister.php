<?php

namespace App\Events;

use App\DB\Models\User;
use App\Helpers\UserHelpers;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;

class UserRegister implements EventInterface
{
    public function __construct(
        public User $user
    ) {
        UserHelpers::AddUserDevice($user->id);
        UserHelpers::createUserActivity($user->id,"User {$user->username} Registered");

    }
//    private function generateVerificationToken()
//    {
//        return bin2hex(random_bytes(32)); // Generate a random string (token)
//    }
//    private function sendVerificationEmail($email, $token)
//    {
//        $verificationLink = "http://yourwebsite.com/verify-email?token=".$this->generateVerificationToken(); // Adjust this URL according to your application
//        $subject = "Email Verification";
//        $message = "Please click the following link to verify your email address: $verificationLink";
//
//        Mail::to($email)->send(new VerificationEmail($subject, $message));
//    }

}