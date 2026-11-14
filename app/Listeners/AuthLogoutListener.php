<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthLogoutListener
{
    public function handle(Logout $event): void
    {
        /** @var User|null $user */
        $user = $event->user;

        Log::channel('auth')->info('Logged Out', [
            'user' => $user->username ?? '',
            'address' => Request::ip(),
        ]);
    }
}
