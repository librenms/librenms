<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->registered(function ($app): void {
        $app->usePublicPath(path: realpath(base_path('html')));
    })
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (): void {
            Illuminate\Support\Facades\Route::prefix('api/v1')
                ->name('api.v1.')
                ->middleware('api.v1')
                ->group(base_path('routes/api-v1.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/auth/*/callback',
        ]);

        $middleware->authenticateSessions();

        $middleware->web([
            \App\Http\Middleware\CheckInstalled::class,
            \App\Http\Middleware\AutoLoginTestUser::class,
            \App\Http\Middleware\LegacyExternalAuth::class,
            \App\Http\Middleware\VerifyUserEnabled::class,
            \App\Http\Middleware\VerifyTwoFactor::class,
            \App\Http\Middleware\LoadUserPreferences::class,
        ]);

        $middleware->api([
            \App\Http\Middleware\EnforceJson::class,  // prevent redirect to login page
            \App\Http\Middleware\LogApiAuthFailure::class,
            'auth:sanctum',
        ]);

        // must wrap authentication to see its failures
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\LogApiAuthFailure::class,
        );

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);
        $middleware->replace(\Illuminate\Http\Middleware\HandleCors::class, \App\Http\Middleware\HandleCors::class);

        $middleware->group('api.v1', [
            \App\Http\Middleware\FormatJsonApiError::class, // first, so it formats the errors of the others
            \App\Http\Middleware\EnsureApiV1Enabled::class,
            \App\Http\Middleware\EnforceJsonApi::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'deny-demo' => \App\Http\Middleware\DenyDemoUser::class,
            'saved-filter' => \App\Http\Middleware\MergeSavedFilter::class,
        ]);

        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\EnforceJson::class, // must be before auth
            \App\Http\Middleware\AutoLoginTestUser::class, // must be before auth
            \App\Http\Middleware\LegacyExternalAuth::class, // must be before auth
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        new \App\Exceptions\ErrorReporting($exceptions);

        // unmatched v1 routes, such as unknown endpoints, never reach the api.v1 middleware
        $exceptions->respond(fn (Symfony\Component\HttpFoundation\Response $response, Throwable $e, Request $request) => $request->route() === null && $request->is('api/v1', 'api/v1/*')
            ? (new \App\Http\Middleware\FormatJsonApiError)->handle($request, fn () => $response)
            : $response);
    })
    ->booting(function (Application $app): void {
        // Resolve the exception handler early so ErrorReporting installs its error handler
        // after Laravel's HandleExceptions bootstrapper, otherwise warnings become exceptions in production
        $app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class);
    })
    ->create();
