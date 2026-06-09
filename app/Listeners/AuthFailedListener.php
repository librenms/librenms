<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthFailedListener
{
    public function handle(Failed $event): void
    {
        $username = $event->credentials['username'] ?? 'Not found';

        Log::channel('auth')->info('Failed', [
            'user' => $username,
            'address' => Request::ip(),
        ]);
    }
}
