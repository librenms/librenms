<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthLogoutListener
{
    public function handle(Logout $event): void
    {
        /** @var User $user */
        $user = $event->user ?: (object) ['username' => 'Not found'];

        Log::channel('auth')->info('Logged Out', [
            'user' => $user->username,
            'address' => Request::ip(),
        ]);
    }
}
