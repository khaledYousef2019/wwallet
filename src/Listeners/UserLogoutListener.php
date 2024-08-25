<?php

namespace App\Listeners;

use App\Events\TokenChanged;
use App\Events\UserLogout;
use App\Services\Events;
use App\Services\SessionTable;
use Carbon\Carbon;

class UserLogoutListener
{
    public function handle(UserLogout $event): void{
        Events::dispatch(new TokenChanged($event->token, 'deleted'));
    }
}