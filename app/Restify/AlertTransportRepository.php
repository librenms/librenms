<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\AlertOperationController;
use App\Http\Controllers\Api\V1\AlertTransportController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

/**
 * Alert transports and operations: custom v1 routes, authenticated by default.
 */
class AlertTransportRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('alert/transports', [AlertTransportController::class, 'index'])->name('api.v1.alert.transports.index');
        $router->post('alert/transports', [AlertTransportController::class, 'store'])->name('api.v1.alert.transports.store');
        $router->delete('alert/transports/{transport}', [AlertTransportController::class, 'destroy'])->name('api.v1.alert.transports.destroy');
        $router->get('alert/operations', [AlertOperationController::class, 'index'])->name('api.v1.alert.operations.index');
        $router->post('alert/operations/{operation}/transports', [AlertOperationController::class, 'attachTransport'])->name('api.v1.alert.operations.transports.attach');
        $router->delete('alert/operations/{operation}/transports/{transport}', [AlertOperationController::class, 'detachTransport'])->name('api.v1.alert.operations.transports.detach');
    }
}
