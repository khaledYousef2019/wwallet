<?php

namespace App\Events;

use App\DB\Models\Token;

class TokenChanged implements EventInterface
{
    public function __construct(public Token $token, public string $action)
    {
    }
}
