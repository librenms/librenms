<?php

namespace App\Restify;

use App\Http\Controllers\Api\V1\SystemController;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Routing\Router;

class SystemRepository extends RestifyRepository
{
    public static string $uriKey = '';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function routes(Router $router, array $attributes, $wrap = true): void
    {
        $router->get('system', SystemController::class)
            ->name('api.v1.system');
    }
}
