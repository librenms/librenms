<?php

namespace App\Listeners;

use App\Facades\LibrenmsConfig;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuthFailedListener
{
    public function handle(Failed $event): void
    {
        if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
            Log::warning('Active Directory authentication failed', [
                'username' => $event->credentials['samaccountname'] ?? $event->credentials['userprincipalname'] ?? '',
                'user_id' => $event->user?->getAuthIdentifier(),
                'reason' => $event->user === null ? 'No LDAP user found and no matching local fallback account' : 'Password validation or an access rule failed',
            ]);
            DB::table('authlog')->insert([
                'user' => $event->credentials['samaccountname'] ?? $event->credentials['userprincipalname'] ?? '',
                'address' => Request::ip(),
                'result' => 'Invalid credentials',
            ]);
        }
    }
}
