<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthFailedListener
{
    public function handle(Failed $event): void
    {
        // when the user exists, the user provider logs the reason the credentials were rejected
        if ($event->user !== null) {
            return;
        }

        Log::channel('auth')->warning('User not found', [
            'user' => $event->credentials['username'] ?? '',
            'address' => Request::ip(),
        ]);
    }
}
