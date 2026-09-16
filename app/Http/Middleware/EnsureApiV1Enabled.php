<?php

namespace App\Http\Middleware;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiV1Enabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! LibrenmsConfig::get('api.v1.enabled', false)) {
            abort(404);
        }

        return $next($request);
    }
}
