<?php

namespace App\Listeners;

use App\Events\TokenChanged;
use App\Events\UserLogout;
use App\Services\Events;

class UserLoginListener
{
    public function handle(UserLogout $event): void{
//        Events::dispatch(new TokenChanged($event->token, 'created'));
    }
}