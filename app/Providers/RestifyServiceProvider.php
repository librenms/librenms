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
        if (! self::apiV1Enabled()) {
            return;
        }

        // v1 custom endpoints that are not Restify repositories.
        Route::prefix('api/v1')->group(base_path('routes/api_v1.php'));

        parent::routes();
    }

    /**
     * Whether the v1 API is enabled, decided at route-registration time.
     *
     * This runs while providers are still booting, so it must not touch
     * LibrenmsConfig: constructing ConfigRepository pre-boot skips loadDB()
     * (Eloquent::isConnected() is false until the app has booted) and caches
     * a config without any DB settings for CONFIG_CACHE_TTL, both disabling
     * this gate and poisoning the shared config cache. A plain Eloquent
     * query works fine pre-boot, so the setting is read directly. Note this
     * intentionally ignores config.php overrides for this one setting.
     */
    public static function apiV1Enabled(): bool
    {
        try {
            return (bool) \App\Models\Config::query()
                ->where('config_name', 'api.v1.enabled')
                ->value('config_value');
        } catch (\Throwable) {
            // no database yet (install, package:discover, pre-migration)
            return false;
        }
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
