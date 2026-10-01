<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\HealthController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

class HealthRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('health', HealthController::class)
            ->withoutMiddleware('auth:sanctum')
            ->middleware('throttle:60,1')
            ->name('api.v1.health');
    }
}
