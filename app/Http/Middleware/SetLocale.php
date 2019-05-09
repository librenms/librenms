<?php

namespace App\Http\Middleware;

use App;
use App\Models\UserPref;
use Closure;

class SetLocale
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
        if ($locale = UserPref::getPref($request->user(), 'locale')) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
