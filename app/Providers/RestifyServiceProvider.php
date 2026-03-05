<?php

namespace App\Providers;

use App\Restify\AlertRuleRepository;
use App\Restify\AlertTemplateRepository;
use App\Restify\DeviceGroupRepository;
use App\Restify\DeviceRepository;
use App\Restify\PortRepository;
use App\Restify\UserRepository;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;

class RestifyServiceProvider extends RestifyApplicationServiceProvider
{
    protected function gate(): void
    {
        Gate::define("viewRestify", function ($user = null) {
            return true;
        });
    }

    protected function routes(): void
    {
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

        Restify::repositories([
            DeviceRepository::class,
            PortRepository::class,
            UserRepository::class,
            AlertTemplateRepository::class,
            AlertRuleRepository::class,
            DeviceGroupRepository::class,
        ]);
    }
}
