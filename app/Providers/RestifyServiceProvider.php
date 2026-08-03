<?php

namespace App\Providers;

use App\Api\CustomRestifyRoutesBoot;
use App\Facades\LibrenmsConfig;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class RestifyServiceProvider extends RestifyApplicationServiceProvider
{
    /**
     * Repository classes exposed on the v1 API.
     */
    protected array $repositories = [
        // DeviceRepository::class,
    ];

    protected function gate(): void
    {
        Gate::define('viewRestify', fn ($user = null) => true);
    }

    protected function routes(): void
    {
        if (! LibrenmsConfig::get('api.v1.enabled', false)) {
            return;
        }

        // v1 custom endpoints that are not Restify repositories.
        Route::prefix('api/v1')->group(base_path('routes/api_v1.php'));

        parent::routes();
    }

    public function boot(): void
    {
        // Use our own route booter so Restify skips its built-in routes
        // (profile, search, restifyjs/setup) — only repositories and our
        // ping/health controllers should be exposed. Must bind before
        // parent::boot(), since that's what triggers route registration.
        $this->app->bind(RoutesBoot::class, CustomRestifyRoutesBoot::class);

        parent::boot();

        Restify::repositories($this->repositories);
    }
}
