<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\DeviceMetricsController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

/**
 * Current device metrics: custom v1 routes, authenticated by default.
 */
class DeviceMetricsRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('devices/{device}/metrics/summary', [DeviceMetricsController::class, 'summary'])->name('api.v1.devices.metrics.summary');
        $router->get('devices/{device}/metrics/realtime', [DeviceMetricsController::class, 'realtime'])->name('api.v1.devices.metrics.realtime');
        $router->get('devices/{device}/metrics/live', [DeviceMetricsController::class, 'live'])->name('api.v1.devices.metrics.live');
        $router->get('devices/{device}/metrics/live/load', [DeviceMetricsController::class, 'liveLoad'])->name('api.v1.devices.metrics.live-load');
        $router->get('devices/{device}/metrics/live/network', [DeviceMetricsController::class, 'liveNetwork'])->name('api.v1.devices.metrics.live-network');
        $router->get('devices/{device}/metrics/io-wait', [DeviceMetricsController::class, 'ioWait'])->name('api.v1.devices.metrics.io-wait');
    }
}
