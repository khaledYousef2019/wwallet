<?php

namespace App\Events;

use App\DB\Models\Token;
use App\DB\Models\User;

class UserLogout implements EventInterface
{
    /**
     * @throws \Exception
     */
    public function __construct(
        public User $user
    ) {
//        Token::deleteToken($token);
    }
}