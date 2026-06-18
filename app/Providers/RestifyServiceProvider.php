<?php

namespace App\Providers;

use App\Http\Controllers\Api\V1\HealthController;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class RestifyServiceProvider extends RestifyApplicationServiceProvider
{
    protected function gate(): void
    {
        Gate::define('viewRestify', function ($user = null) {
            return true;
        });
    }

    protected function routes(): void
    {
        // v1 custom endpoints that are not Restify repositories. The health
        // endpoint is intentionally public (no auth middleware).
        Route::prefix('api/v1')->group(function (): void {
            Route::get('health', HealthController::class)->name('v1.health');
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
        parent::boot();

        // No model repositories are registered yet. Add repository classes
        // here as v1 resources are introduced.
        Restify::repositories([]);
    }
}
