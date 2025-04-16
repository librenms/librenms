<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class AuthLoginListener
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        DB::table('authlog')->insert(['user' => $user->username ?: '', 'address' => Request::ip(), 'result' => 'Logged In']);

        toast()->info('Welcome ' . ($user->realname ?: $user->username));
    }
}
