<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LibreNMS\Config;
use Symfony\Component\HttpFoundation\Response;

class LoadUserPreferences
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $preferences = ['locale', 'site_style', 'timezone'];
        $this->loadPreferences($request, $preferences);

        $this->setPreference($request, 'locale', function ($locale) {
            app()->setLocale($locale);
        });

        $this->setPreference($request, 'site_style', function ($style) {
            Config::set('applied_site_style', $style);
        });

        $this->setPreference($request, 'timezone', function ($timezone) use ($request) {
            $request->session()->put('preferences.timezone', $timezone);
            $request->session()->put('preferences.timezone_static', true);
        });

        return $next($request);
    }

    /**
     * Fetch preferences from the database
     * Load all preferences at once if we need to query the database
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $preferences
     */
    private function loadPreferences($request, $preferences)
    {
        if (! $request->session()->has('preferences') && ! is_null($request->user())) {
            $loaded = $request->user()->preferences()->whereIn('pref', $preferences)->pluck('value', 'pref');
            $request->session()->put('preferences', $loaded);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $pref
     * @param  callable  $callable
     */
    private function setPreference($request, $pref, $callable)
    {
        $value = $request->session()->get("preferences.$pref");
        if ($value !== null) {
            $callable($value);
        }
    }
}
