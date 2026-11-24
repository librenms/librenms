<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\PortRealtimeController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

class PortRealtimeRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('ports/{portId}/realtime', PortRealtimeController::class)
            ->whereNumber('portId')
            ->name('api.v1.ports.realtime');
    }
}
