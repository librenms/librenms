<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->registered(function ($app) {
        $app->usePublicPath(path: realpath(base_path('html')));
    })
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/auth/*/callback',
        ]);

        $middleware->authenticateSessions();

        $middleware->web([
            \App\Http\Middleware\CheckInstalled::class,
            \App\Http\Middleware\LegacyExternalAuth::class,
            \App\Http\Middleware\VerifyUserEnabled::class,
            \App\Http\Middleware\VerifyTwoFactor::class,
            \App\Http\Middleware\LoadUserPreferences::class,
        ]);

        $middleware->api([
            \App\Http\Middleware\EnforceJson::class,  // prevent redirect to login page
            'auth:token',
        ]);

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);
        $middleware->replace(\Illuminate\Http\Middleware\HandleCors::class, \App\Http\Middleware\HandleCors::class);

        $middleware->alias([
            'deny-demo' => \App\Http\Middleware\DenyDemoUser::class,
        ]);

        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\EnforceJson::class, // must be before auth
            \App\Http\Middleware\LegacyExternalAuth::class, // must be before auth
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        new \App\Exceptions\ErrorReporting($exceptions);
    })->create();
