<?php

namespace App\Providers;

use App\Restify\RestifyPolicyCommand;
use App\Restify\RoutesBoot as CustomRoutesBoot;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Commands\PolicyCommand;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\ServiceProvider;

class RestifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RoutesBoot::class, CustomRoutesBoot::class);
        $this->app->bind(PolicyCommand::class, RestifyPolicyCommand::class);

        if (class_exists(RestifyApplicationServiceProvider::class)) {
            $this->app->register(RestifyApplicationServiceProvider::class);
        }
    }

    public function boot(): void
    {
        // Parent only registers routes in console (for route:list)
        if ($this->app->runningUnitTests()) {
            app(RoutesBoot::class)->boot();
        }
    }
}
