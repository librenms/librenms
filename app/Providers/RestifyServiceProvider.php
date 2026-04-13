<?php

namespace App\Providers;

use App\Restify\AccessPointRepository;
use App\Restify\ApplicationRepository;
use App\Restify\BgpPeerRepository;
use App\Restify\BillRepository;
use App\Restify\AlertLogRepository;
use App\Restify\AlertRepository;
use App\Restify\AlertRuleRepository;
use App\Restify\AlertScheduleRepository;
use App\Restify\AlertTemplateRepository;
use App\Restify\AlertTransportRepository;
use App\Restify\CefSwitchingRepository;
use App\Restify\ComponentRepository;
use App\Restify\AuthLogRepository;
use App\Restify\DeviceGroupRepository;
use App\Restify\DeviceOutageRepository;
use App\Restify\DeviceRepository;
use App\Restify\DiskIoRepository;
use App\Restify\EventlogRepository;
use App\Restify\AvailabilityRepository;
use App\Restify\InventoryRepository;
use App\Restify\IpsecTunnelRepository;
use App\Restify\Ipv4AddressRepository;
use App\Restify\Ipv4MacRepository;
use App\Restify\Ipv4NetworkRepository;
use App\Restify\Ipv6AddressRepository;
use App\Restify\Ipv6NdRepository;
use App\Restify\Ipv6NetworkRepository;
use App\Restify\IsisAdjacencyRepository;
use App\Restify\LinkRepository;
use App\Restify\LocationRepository;
use App\Restify\MempoolRepository;
use App\Restify\MplsLspPathRepository;
use App\Restify\MplsLspRepository;
use App\Restify\MplsSapRepository;
use App\Restify\MplsSdpBindRepository;
use App\Restify\MplsSdpRepository;
use App\Restify\MplsServiceRepository;
use App\Restify\MplsTunnelArHopRepository;
use App\Restify\MplsTunnelCHopRepository;
use App\Restify\OspfAreaRepository;
use App\Restify\OspfInstanceRepository;
use App\Restify\OspfNbrRepository;
use App\Restify\OspfPortRepository;
use App\Restify\Ospfv3AreaRepository;
use App\Restify\Ospfv3InstanceRepository;
use App\Restify\Ospfv3NbrRepository;
use App\Restify\Ospfv3PortRepository;
use App\Restify\PollerClusterRepository;
use App\Restify\PollerClusterStatRepository;
use App\Restify\PollerGroupRepository;
use App\Restify\PortGroupRepository;
use App\Restify\PortAdslRepository;
use App\Restify\PortRepository;
use App\Restify\PortSecurityRepository;
use App\Restify\PortsFdbRepository;
use App\Restify\PortsNacRepository;
use App\Restify\PortStackRepository;
use App\Restify\PortStatisticRepository;
use App\Restify\PortStpRepository;
use App\Restify\PortVdslRepository;
use App\Restify\PortVlanRepository;
use App\Restify\ProcessorRepository;
use App\Restify\PseudowireRepository;
use App\Restify\RouteRepository;
use App\Restify\SensorRepository;
use App\Restify\ServiceRepository;
use App\Restify\SlaRepository;
use App\Restify\ServiceTemplateRepository;
use App\Restify\StpRepository;
use App\Restify\StorageRepository;
use App\Restify\SyslogRepository;
use App\Restify\TransceiverRepository;
use App\Restify\UserRepository;
use App\Restify\WirelessSensorRepository;
use App\Restify\VlanRepository;
use App\Restify\VrfLiteRepository;
use App\Restify\VrfRepository;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SystemController;
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
        // v1 custom endpoints that are not Restify repositories. Registered
        // before RoutesBoot so they win over Restify's catch-all routes.
        Route::prefix('api/v1')->group(function (): void {
            Route::get('health', HealthController::class)->name('v1.health');
            Route::get('system', SystemController::class)
                ->middleware(['auth:sanctum', 'can:settings.view'])
                ->name('v1.system');
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
            AlertLogRepository::class,
            AlertRepository::class,
            AlertRuleRepository::class,
            AlertScheduleRepository::class,
            AlertTemplateRepository::class,
            AlertTransportRepository::class,
            AccessPointRepository::class,
            ApplicationRepository::class,
            AuthLogRepository::class,
            BgpPeerRepository::class,
            BillRepository::class,
            CefSwitchingRepository::class,
            ComponentRepository::class,
            AvailabilityRepository::class,
            DeviceGroupRepository::class,
            DeviceOutageRepository::class,
            DeviceRepository::class,
            DiskIoRepository::class,
            EventlogRepository::class,
            InventoryRepository::class,
            IpsecTunnelRepository::class,
            Ipv4AddressRepository::class,
            Ipv4MacRepository::class,
            Ipv4NetworkRepository::class,
            Ipv6AddressRepository::class,
            Ipv6NdRepository::class,
            Ipv6NetworkRepository::class,
            IsisAdjacencyRepository::class,
            LinkRepository::class,
            LocationRepository::class,
            MempoolRepository::class,
            MplsLspPathRepository::class,
            MplsLspRepository::class,
            MplsSapRepository::class,
            MplsSdpBindRepository::class,
            MplsSdpRepository::class,
            MplsServiceRepository::class,
            MplsTunnelArHopRepository::class,
            MplsTunnelCHopRepository::class,
            OspfAreaRepository::class,
            OspfInstanceRepository::class,
            OspfNbrRepository::class,
            OspfPortRepository::class,
            Ospfv3AreaRepository::class,
            Ospfv3InstanceRepository::class,
            Ospfv3NbrRepository::class,
            Ospfv3PortRepository::class,
            PollerClusterRepository::class,
            PollerClusterStatRepository::class,
            PollerGroupRepository::class,
            PortAdslRepository::class,
            PortGroupRepository::class,
            PortRepository::class,
            PortSecurityRepository::class,
            PortsFdbRepository::class,
            PortsNacRepository::class,
            PortStackRepository::class,
            PortStatisticRepository::class,
            PortStpRepository::class,
            PortVdslRepository::class,
            PortVlanRepository::class,
            ProcessorRepository::class,
            PseudowireRepository::class,
            RouteRepository::class,
            SensorRepository::class,
            ServiceRepository::class,
            ServiceTemplateRepository::class,
            SlaRepository::class,
            StpRepository::class,
            StorageRepository::class,
            SyslogRepository::class,
            TransceiverRepository::class,
            UserRepository::class,
            VlanRepository::class,
            VrfLiteRepository::class,
            VrfRepository::class,
            WirelessSensorRepository::class,
        ]);
    }
}
