<?php

namespace App\Providers;

use App\Api\CustomRestifyRoutesBoot;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class RestifyServiceProvider extends RestifyApplicationServiceProvider
{
    /**
     * Repository classes exposed on the v1 API.
     *
     * @var array<int, class-string<\Binaryk\LaravelRestify\Repositories\Repository>>
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
        // Gate on Laravel config (API_V1_ENABLED env), not LibrenmsConfig:
        // this runs while providers are still booting, and constructing
        // LibrenmsConfig pre-boot caches a config built without any DB
        // settings (Eloquent::isConnected() is false until the app has
        // booted), poisoning the shared config cache for CONFIG_CACHE_TTL.
        if (! config('api.v1.enabled')) {
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
