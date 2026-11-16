<?php

namespace App\Listeners;

use App\Facades\LibrenmsConfig;
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
        $isActiveDirectory = LibrenmsConfig::get('auth_mechanism') === 'active_directory';

        if ($isActiveDirectory) {
            Log::warning('Active Directory authentication failed', [
                'username' => $identifier ?? '',
                'user_id' => $event->user?->getAuthIdentifier(),
                'reason' => $event->user === null ? 'No LDAP user found and no matching local fallback account' : 'Password validation or an access rule failed',
            ]);
        }

        Log::channel('auth')->warning(! $isActiveDirectory && $event->user === null ? 'User not found' : 'Invalid credentials', [
            'user' => $identifier ?? '',
            'address' => Request::ip(),
        ]);
    }
}
