<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthLoginListener
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        Log::channel('auth')->info('Logged In', [
            'user' => $user->username,
            'address' => Request::ip(),
        ]);

        toast()->info('Welcome ' . ($user->realname ?: $user->username));
    }
}
