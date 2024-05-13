<?php

namespace App\Http\Services;

use App\DB\Models\User;

class AuthService
{
    public function signUpProcess($request)
    {
        $data = ['success' => false, 'data' => [], 'message' => __('Something went wrong')];

        try {
            $mail_key = $this->generate_email_verification_key();
            $user = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => USER_ROLE_USER,
                'password' => Hash::make($request['password']),
            ]);
//            UserVerificationCode::create(['user_id' => $user->id, 'code' => $mail_key, 'expired_at' => date('Y-m-d', strtotime('+15 days'))]);
//
//            $coin = Coin::where('type', DEFAULT_COIN_TYPE)->first();
//            Wallet::create([
//                'user_id' => $user->id,
//                'name' => 'Default wallet',
//                'is_primary' => STATUS_SUCCESS,
//                'coin_id' => $coin->id,
//                'coin_type' => $coin->type,
//            ]);
//            app(CommonService::class)->generateNewCoinWallet($user->id);

            DB::commit();

            if (!empty($user)){
                $this->sendVerifyemail($user, $mail_key);
                $data = ['success' => true, 'data' => [], 'message' => __('Sign up successful, Please verify your mail')];

            } else {
                $data = ['success' => false, 'data' => [], 'message' => __('Sign up failed')];
            }
        } catch (\Exception $e) {
            $this->logger->log('signUpProcess', $e->getMessage());
            DB::rollback();
            $data = ['success' => false, 'data' => [], 'message' => __('Something went wrong')];
        }

        return $data;
    }

    private function generate_email_verification_key()
    {
        $key = randomNumber(6);
        return $key;
    }

}