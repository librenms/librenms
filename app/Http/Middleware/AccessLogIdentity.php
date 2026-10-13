<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AccessLogIdentity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->remove('X-LibreNMS-User-ID');
        $response->headers->remove('X-LibreNMS-Token-ID');

        $user = $request->user();
        if ($user) {
            $response->headers->set('X-LibreNMS-User-ID', (string) $user->getAuthIdentifier());

            $token = $request->is('api/*') ? $user->currentAccessToken() : null;
            if ($token instanceof PersonalAccessToken) {
                $response->headers->set('X-LibreNMS-Token-ID', (string) $token->getKey());
            }
        }

        return $response;
    }
}
