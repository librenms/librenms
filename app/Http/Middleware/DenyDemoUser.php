<?php

namespace App\Http\Middleware;

use Closure;

class DenyDemoUser
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
        if ($request->user()->isDemo()) {
            return response()->view('auth.deny-demo');
        }

        return $next($request);
    }
}
