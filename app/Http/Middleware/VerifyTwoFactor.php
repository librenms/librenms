<?php

namespace App\Http\Middleware;

use App\Models\UserPref;
use Closure;
use Illuminate\Support\Str;
use LibreNMS\Config;

class VerifyTwoFactor
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
        // check twofactor
        if (Config::get('twofactor') === true) {
            // don't apply on 2fa checking routes
            $route_name = $request->route()->getName();
            if ($route_name && Str::startsWith($route_name, '2fa.')) {
                return $next($request);
            }

            $twofactor = $request->session()->get('twofactoradd', UserPref::getPref($request->user(), 'twofactor'));

            if (! empty($twofactor)) {
                // user has 2fa enabled
                if (! $request->session()->get('twofactor')) {
                    // verification is needed
                    return redirect('/2fa');
                }
            }
        }

        return $next($request);
    }
}
