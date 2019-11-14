<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use LibreNMS\Config;

class LoadUserPreferences
{
    private $preferences;

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
            Config::set('site_style_default', Config::get('site_style'));
            Config::set('site_style', $style);
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
        if (!$request->session()->has('preferences') && !is_null($request->user())) {
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
