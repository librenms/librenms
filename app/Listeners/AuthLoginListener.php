<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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
