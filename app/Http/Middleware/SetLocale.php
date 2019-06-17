<?php

namespace App\Http\Middleware;

use App;
use App\Models\UserPref;
use Closure;
use Session;

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
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } elseif (!is_null($request->user())) {
            $locale = UserPref::getPref($request->user(), 'locale');
            Session::put('locale', $locale);
        }

        if (!empty($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
