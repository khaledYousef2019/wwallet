<?php

namespace App\Events;

use App\DB\Models\User;

class UserLogin implements EventInterface
{
    public function __construct(
        public User $user
    ) {}
}