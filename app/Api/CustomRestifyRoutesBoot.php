<?php

namespace App\Api;

use Binaryk\LaravelRestify\Bootstrap\RoutesBoot as BaseRoutesBoot;
use Binaryk\LaravelRestify\Bootstrap\RoutesDefinition;
use Illuminate\Support\Facades\Route;

/**
 * Customized Restify route booter.
 *
 * The base class registers a set of built-in, non-repository endpoints
 * (profile, global search, restifyjs/setup) via RoutesDefinition::once().
 * We don't want those: the only v1 routes should be the ones we explicitly
 * add (registered repositories and our own custom controllers ).
 * This override registers the per-repository CRUD routes but
 * skips once().
 */
class CustomRestifyRoutesBoot extends BaseRoutesBoot
{
    /**
     * @param  array<string, mixed>  $config  Route group config (prefix, middleware, name, ...).
     */
    public function defaultRoutes($config): self
    {
        Route::group($config, function (): void {
            // Skip ->once() to drop Restify's built-in profile,
            // search, and restifyjs/setup routes.
            app(RoutesDefinition::class)();
        });

        return $this;
    }
}
