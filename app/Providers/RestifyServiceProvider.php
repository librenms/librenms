<?php

namespace App\Providers;

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\PingController;
use App\Restify\RoutesBoot as CustomRoutesBoot;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class RestifyServiceProvider extends RestifyApplicationServiceProvider
{
    protected function gate(): void
    {
        Gate::define('viewRestify', fn ($user = null) => true);
    }

    protected function routes(): void
    {
        // v1 custom endpoints that are not Restify repositories.
        Route::prefix('api/v1')->group(function (): void {
            // Public, unauthenticated liveness probe.
            Route::get('ping', PingController::class)->name('v1.ping');

            // Health check requires authentication (exposes subsystem status).
            Route::get('health', HealthController::class)
                ->middleware('auth:sanctum')
                ->name('v1.health');
        });

        parent::routes();

        // Parent only registers routes in console (for route:list) and
        // skips both web requests and unit tests. Register for both.
        if (! app()->runningInConsole() || app()->runningUnitTests()) {
            app(RoutesBoot::class)->boot();
        }
    }

    public function boot(): void
    {
        // Swap Restify's route booter for one that omits the built-in
        // default routes (profile, global search, restifyjs/setup). The only
        // v1 routes should be those we explicitly add: registered
        // repositories and our own custom controllers (ping/health). This
        // must be bound before parent::boot() triggers route registration,
        // and persists for the RestifyInjector middleware at request time.
        $this->app->bind(RoutesBoot::class, CustomRoutesBoot::class);

        parent::boot();

        // No model repositories are registered yet. Add repository classes
        // here as v1 resources are introduced.
        Restify::repositories([]);
    }
}
