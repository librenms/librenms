<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Routing\Router;

class HealthRepository extends Repository
{
    public static string $uriKey = '';

    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('health', HealthController::class)
            ->name('api.v1.health');
    }
}
