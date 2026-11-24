<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\GraphDataController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

class GraphDataRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('graph-data', GraphDataController::class)
            ->name('api.v1.graph-data');
    }
}
