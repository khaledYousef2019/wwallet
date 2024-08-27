<?php

namespace App\Listeners;

use App\Events\TokenChanged;
use App\Services\SessionTable;
use Carbon\Carbon;

class TokenEventListener
{
    public function handle(TokenChanged $event)
    {
        $sessionTable = SessionTable::getInstance();

        if ($event->action === 'created') {

            $sessionTable->set($event->token->token, [
                'user_id' => $event->token->user_id,
                'device' => $event->token->device,
                'ip' => $event->token->ip,
                'ttl' => Carbon::now()->addMinutes(1)->getTimestamp()
            ]);
        } elseif ($event->action === 'deleted') {
            $sessionTable->delete($event->token->token);
        }
    }
}
