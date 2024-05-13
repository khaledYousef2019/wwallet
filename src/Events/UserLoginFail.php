<?php

namespace App\Events;

class UserLoginFail implements EventInterface
{
    public function __construct(
        public string $email
    ) {}
}