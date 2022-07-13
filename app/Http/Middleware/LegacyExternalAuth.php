<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Authentication\LegacyAuth;

class LegacyExternalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! Auth::guard($guard)->check()) {
            // check for get variables
            if ($request->isMethod('get') && $request->has(['username', 'password'])) {
                Auth::attempt($request->only(['username', 'password']));
            }

            if (LegacyAuth::get()->authIsExternal()) {
                $credentials = [
                    'username' => LegacyAuth::get()->getExternalUsername(),
                    'password' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
                ];

                if (! Auth::guard($guard)->attempt($credentials)) {
                    $message = ''; // no debug info for now...

                    // force user to failure page
                    return response(view('auth.external-auth-failed')->with('message', $message));
                }
            }
        }

        return $next($request);
    }
}
