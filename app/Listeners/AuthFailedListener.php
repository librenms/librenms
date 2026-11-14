<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthFailedListener
{
    public function handle(Failed $event): void
    {
        // the identifier is whichever credential is not the password (username, email, ...)
        $identifier = Arr::first(Arr::except($event->credentials, 'password'), fn ($value) => is_string($value));

        Log::channel('auth')->warning($event->user === null ? 'User not found' : 'Invalid credentials', [
            'user' => $identifier ?? '',
            'address' => Request::ip(),
        ]);
    }
}
