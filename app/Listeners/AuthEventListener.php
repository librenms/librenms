<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Request;

class AuthEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the failed auth event.
     *
     * @param  Failed  $event
     * @return void
     */
    public function failed(Failed $event)
    {
        $username = $event->credentials['username'] ?? 'Not found';

        Log::channel('auth')->info('Failed', [
            'user' => $username,
            'address' => Request::ip(),
        ]);
    }

    /**
     * Handle the login event.
     *
     * @param  Login  $event
     * @return void
     */
    public function login(Login $event)
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        Log::channel('auth')->info('Logged In', [
            'user' => $user->username,
            'address' => Request::ip(),
        ]);

        toast()->info('Welcome ' . ($user->realname ?: $user->username));
    }

    /**
     * Handle the logout event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function logout(Logout $event)
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        Log::channel('auth')->info('Logged Out', [
            'user' => $user->username,
            'address' => Request::ip(),
        ]);
    }
}
