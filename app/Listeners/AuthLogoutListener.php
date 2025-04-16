<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;

class AuthLogoutListener
{
    public function handle(Logout $event): void
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        DB::table('authlog')->insert(['user' => $user->username ?: '', 'address' => Request::ip(), 'result' => 'Logged Out']);
    }
}
