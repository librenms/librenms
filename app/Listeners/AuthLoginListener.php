<?php

namespace App\Listeners;

use App\Facades\LibrenmsConfig;
use App\Ldap\RememberedActiveDirectoryLogin;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use LdapRecord\Laravel\Auth\DatabaseUserProvider;

class AuthLoginListener
{
    public function __construct(private readonly RememberedActiveDirectoryLogin $rememberedLogin) {}

    public function handle(Login $event): ?false
    {
        /** @var User $user */
        $user = $event->user;

        if ($this->rejectRememberedLogin($event, $user)) {
            return false;
        }

        Log::channel('auth')->info('Logged In', [
            'user' => $user->username,
            'address' => Request::ip(),
        ]);

        toast()->info('Welcome ' . ($user->realname ?: $user->username));

        return null;
    }

    private function rejectRememberedLogin(Login $event, User $user): bool
    {
        if ($event->guard !== 'web' || ! $event->remember || LibrenmsConfig::get('auth_mechanism') !== 'active_directory') {
            return false;
        }

        $guard = Auth::guard('web');
        // The guard loads the recalled user before firing Login. A password login sets the user after the event.
        if (! $guard instanceof SessionGuard || ! $guard->viaRemember() || $guard->getUser() !== $user) {
            return false;
        }

        $provider = $guard->getProvider();
        if (! $provider instanceof DatabaseUserProvider || $this->rememberedLogin->allows($user, $provider->getLdapUserRepository())) {
            return false;
        }

        $guard->logoutCurrentDevice();
        $guard->getSession()->invalidate();
        $guard->getSession()->regenerateToken();

        return true;
    }
}
