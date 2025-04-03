<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\ErrorReportingProvider::class, // This should always be after the config is loaded,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/auth/*/callback',
        ]);

        $middleware->append(\App\Http\Middleware\HandleCors::class);

        $middleware->web([
            \App\Http\Middleware\CheckInstalled::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\VerifyUserEnabled::class,
        ]);

        $middleware->api([
            \App\Http\Middleware\EnforceJson::class,
            'authenticate:token',
        ]);

        $middleware->group('auth', [
            \App\Http\Middleware\LegacyExternalAuth::class,
            \App\Http\Middleware\Authenticate::class,
            \App\Http\Middleware\VerifyTwoFactor::class,
            \App\Http\Middleware\LoadUserPreferences::class,
        ]);

        $middleware->group('minimal', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'deny-demo' => \App\Http\Middleware\DenyDemoUser::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\LegacyExternalAuth::class,
            \App\Http\Middleware\Authenticate::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
