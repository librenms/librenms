<?php

namespace App\Http\Middleware;

use App\Models\User;
use Auth;
use Closure;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use LibreNMS\Exceptions\AuthenticationException;
use Log;

class LegacyExternalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check() && LegacyAuth::get()->authIsExternal()) {
            try {
                $username = LegacyAuth::get()->getExternalUsername();
                $password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

                if (LegacyAuth::get()->authenticate($username, $password)) {
                    $user_id = User::thisAuth()->where('username', $username)->value('user_id');
                     Auth::loginUsingId($user_id);
                }
            } catch (AuthenticationException $e) {
                $message = $e->getMessage();
                Log::critical('HTTP Auth Error: ' . $message);

                if (!Config::get('auth.debug', false)) {
                    $message = '';
                }

                // force user to failure page
                return response(view('auth.external-auth-failed')->with('message', $message));
            }
        }

        return $next($request);
    }
}
