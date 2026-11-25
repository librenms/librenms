<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class LogApiAuthFailure
{
    /**
     * Record API requests that presented a token but were not authenticated in the auth log.
     * Token guards do not fire the Failed event the way login attempts do.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (($response->exception ?? null) instanceof AuthenticationException) {
            $token = (string) (Sanctum::$accessTokenRetrievalCallback)($request);

            if ($token !== '') {
                $user = Sanctum::$personalAccessTokenModel::findToken($token)?->tokenable;

                Log::channel('auth')->warning('Invalid API token', [
                    'user' => $user->username ?? '',
                    'address' => $request->ip(),
                ]);
            }
        }

        return $response;
    }
}
