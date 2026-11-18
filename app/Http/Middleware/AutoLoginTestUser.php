<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Development only: log in the user named by AUTH_TEST_USER so the web ui can be used without a login.
 * Does nothing unless APP_ENV=local.
 */
class AutoLoginTestUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('auth.test_user');

        if (is_string($username) && $username !== '' && app()->isLocal() && $request->user() === null) {
            $user = User::firstWhere('username', $username);

            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
