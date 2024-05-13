<?php

namespace App\Events;

use App\DB\Models\User;

class UserLogout implements EventInterface
{
    public function __construct(
        public User $user
    ) {}
}