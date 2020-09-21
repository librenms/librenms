<?php

namespace App\Http\Middleware;

use Closure;
use LibreNMS\Config;

class LoadUserPreferences
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
        $preferences = ['locale', 'site_style'];
        $this->loadPreferences($request, $preferences);

        $this->setPreference($request, 'locale', function ($locale) {
            app()->setLocale($locale);
        });

        $this->setPreference($request, 'site_style', function ($style) {
            Config::set('applied_site_style', $style);
        });

        return $next($request);
    }

    /**
     * Fetch preferences from the database
     * Load all preferences at once if we need to query the database
     *
     * @param \Illuminate\Http\Request $request
     * @param array $preferences
     */
    private function loadPreferences($request, $preferences)
    {
        if (! $request->session()->has('preferences') && ! is_null($request->user())) {
            $loaded = $request->user()->preferences()->whereIn('pref', $preferences)->pluck('value', 'pref');
            $request->session()->put('preferences', $loaded);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $pref
     * @param callable $callable
     */
    private function setPreference($request, $pref, $callable)
    {
        $value = $request->session()->get("preferences.$pref");
        if ($value !== null) {
            $callable($value);
        }
    }
}
