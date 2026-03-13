<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyDemoUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->can('demo')) {
            return response()->view('auth.deny-demo');
        }

        return $next($request);
    }
}
