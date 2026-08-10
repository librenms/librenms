<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiV1Enabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('librenms.api.v1.enabled')) {
            abort(404);
        }

        return $next($request);
    }
}
