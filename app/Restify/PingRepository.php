<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\PingController;
use Illuminate\Routing\Router;

class PingRepository extends Repository
{
    public static string $uriKey = '';

    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('ping', PingController::class)
            ->withoutMiddleware('auth:sanctum')
            ->name('api.v1.ping');
    }
}
