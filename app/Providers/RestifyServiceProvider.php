<?php

namespace App\Providers;

use App\Restify\ApplicationRepository;
use App\Restify\BgpPeerRepository;
use App\Restify\BillRepository;
use App\Restify\AlertLogRepository;
use App\Restify\AlertRepository;
use App\Restify\AlertRuleRepository;
use App\Restify\AlertScheduleRepository;
use App\Restify\AlertTemplateRepository;
use App\Restify\AlertTransportRepository;
use App\Restify\ComponentRepository;
use App\Restify\AuthLogRepository;
use App\Restify\DeviceGroupRepository;
use App\Restify\DeviceRepository;
use App\Restify\EventlogRepository;
use App\Restify\LocationRepository;
use App\Restify\MempoolRepository;
use App\Restify\PollerGroupRepository;
use App\Restify\PortGroupRepository;
use App\Restify\PortRepository;
use App\Restify\ProcessorRepository;
use App\Restify\SensorRepository;
use App\Restify\ServiceRepository;
use App\Restify\ServiceTemplateRepository;
use App\Restify\StorageRepository;
use App\Restify\SyslogRepository;
use App\Restify\UserRepository;
use Binaryk\LaravelRestify\Bootstrap\RoutesBoot;
use Binaryk\LaravelRestify\Restify;
use Binaryk\LaravelRestify\RestifyApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

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
        // Register custom v1 action routes BEFORE Restify's catch-all routes
        Route::prefix('api/v1')
            ->middleware(['auth:sanctum'])
            ->group(function () {
                Route::post('alerts/{alertId}/acknowledge', [AlertRepository::class, 'acknowledge'])
                    ->where('alertId', '[0-9]+');
                Route::post('alerts/{alertId}/unmute', [AlertRepository::class, 'unmute'])
                    ->where('alertId', '[0-9]+');
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

        Restify::repositories([
            ApplicationRepository::class,
            AlertLogRepository::class,
            AlertRepository::class,
            AuthLogRepository::class,
            BgpPeerRepository::class,
            BillRepository::class,
            AlertRuleRepository::class,
            AlertScheduleRepository::class,
            AlertTemplateRepository::class,
            AlertTransportRepository::class,
            ComponentRepository::class,
            DeviceGroupRepository::class,
            DeviceRepository::class,
            EventlogRepository::class,
            LocationRepository::class,
            MempoolRepository::class,
            PollerGroupRepository::class,
            PortGroupRepository::class,
            PortRepository::class,
            ProcessorRepository::class,
            SensorRepository::class,
            ServiceRepository::class,
            ServiceTemplateRepository::class,
            StorageRepository::class,
            SyslogRepository::class,
            UserRepository::class,
        ]);
    }
}
