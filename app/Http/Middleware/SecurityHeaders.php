<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Enforce security headers
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', "default-src 'self';");

        $response->headers->set('Permissions-Policy', 'geolocation=(),midi=(),sync-xhr=(),microphone=(),camera=(),magnetometer=(),gyroscope=(),fullscreen=(self),payment=()');

        $response->headers->set('Referrer-Policy', 'no-referrer');

        $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $response->headers->set('X-Frame-Options', 'DENY');

        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
