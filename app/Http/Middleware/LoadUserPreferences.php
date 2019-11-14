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
        $this->loadPreference($request, 'locale', function ($locale) {
            app()->setLocale($locale);
        });

        $this->loadPreference($request, 'site_style', function ($style) {
            Config::set('site_style', $style);
        });

        return $next($request);
    }

    /**
     * Fetch preferences from the databse and return one value
     * Load all preferences at once if we need to query the database
     *
     * @param string $preference
     * @return mixed
     */
    private function getPref($preference)
    {
        if ($this->preferences === null) {
            $preferences = ['locale', 'site_style'];
            $this->preferences = Auth::user()->preferences()->whereIn('pref', $preferences)->pluck('value', 'pref');
        }

        return $this->preferences->get($preference);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string $pref
     * @param callable $callable
     */
    private function loadPreference($request, $pref, $callable)
    {
        $session = $request->session();
        if ($session->has($pref)) {
            $value = $session->get($pref);
            $callable($value);
        } elseif (!is_null($request->user())) {
            $value = $this->getPref($pref);
            $session->put($pref, $value);
            $callable($value);
        }
    }
}
