<?php

namespace App\Restify;

use App\Models\Route;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class RouteRepository extends Repository
{
    public static string $model = Route::class;

    public static string $id = 'route_id';

    public static string $title = 'inetCidrRouteDest';

    public static array $search = [
        'inetCidrRouteDest',
        'inetCidrRouteNextHop',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('context_name')->readonly(),
            field('inetCidrRouteIfIndex')->readonly(),
            field('inetCidrRouteDest')->readonly(),
            field('inetCidrRouteDestType')->readonly(),
            field('inetCidrRoutePfxLen')->readonly(),
            field('inetCidrRoutePolicy')->readonly(),
            field('inetCidrRouteNextHop')->readonly(),
            field('inetCidrRouteNextHopType')->readonly(),
            field('inetCidrRouteMetric1')->readonly(),
            field('inetCidrRouteProto')->readonly(),
            field('inetCidrRouteType')->readonly(),
            field('inetCidrRouteNextHopAS')->readonly(),
            field('updated_at')->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * Routes are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Routes are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
