<?php

namespace App\Http\Middleware;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadUserPreferences
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $preferences = ['locale', 'site_style', 'timezone'];
            $this->loadPreferences($request, $preferences);

            $this->setPreference($request, 'locale', function ($locale): void {
                app()->setLocale($locale);
            });

            $this->setPreference($request, 'site_style', function ($style, $request): void {
                if ($style !== 'device' && $style !== $request->session()->get('applied_site_style')) {
                    $request->session()->put('applied_site_style', $style);
                }
            });

            $this->setPreference($request, 'timezone', function ($timezone, $request): void {
                $request->session()->put('preferences.timezone', $timezone);
                $request->session()->put('preferences.timezone_static', true);
            });
        } elseif (! $request->session()->has('applied_site_style')) {
            // set applied_site_style for unauth sessions (once)
            $site_style = LibrenmsConfig::get('site_style');
            if ($site_style !== 'device') {
                $request->session()->put('applied_site_style', $site_style);
            }
        }

        return $next($request);
    }

    /**
     * Fetch preferences from the database
     * Load all preferences at once if we need to query the database
     *
     * @param  Request  $request
     * @param  array  $preferences
     */
    private function loadPreferences($request, $preferences)
    {
        if (! $request->session()->has('preferences') && ! is_null($request->user())) {
            $loaded = $request->user()->preferences()->whereIn('pref', $preferences)->pluck('value', 'pref');
            $request->session()->put('preferences', $loaded->toArray());
        }
    }

    /**
     * @param  Request  $request
     * @param  string  $pref
     * @param  callable  $callable
     */
    private function setPreference($request, $pref, $callable)
    {
        $value = $request->session()->get("preferences.$pref");
        if ($value !== null) {
            $callable($value, $request);
        }
    }
}
