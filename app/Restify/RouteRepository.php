<?php

namespace App\Restify;

use App\Models\Route;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class RouteRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Route::class;

    public static string $id = 'route_id';

    public static string $title = 'inetCidrRouteDest';

    public static array $search = [
        'inetCidrRouteDest',
        'inetCidrRouteNextHop',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'port_id' => 'integer',
        'context_name' => 'text',
        'inetCidrRouteIfIndex' => 'integer',
        'inetCidrRouteDest' => 'text',
        'inetCidrRouteDestType' => 'text',
        'inetCidrRoutePfxLen' => 'integer',
        'inetCidrRoutePolicy' => 'text',
        'inetCidrRouteNextHop' => 'text',
        'inetCidrRouteNextHopType' => 'text',
        'inetCidrRouteMetric1' => 'integer',
        'inetCidrRouteProto' => 'integer',
        'inetCidrRouteType' => 'integer',
        'inetCidrRouteNextHopAS' => 'integer',
        'updated_at' => 'datetime',
    ];

    public static array $sort = [
        'device_id',
        'port_id',
        'context_name',
        'inetCidrRouteIfIndex',
        'inetCidrRouteDest',
        'inetCidrRouteDestType',
        'inetCidrRoutePfxLen',
        'inetCidrRoutePolicy',
        'inetCidrRouteNextHop',
        'inetCidrRouteNextHopType',
        'inetCidrRouteMetric1',
        'inetCidrRouteProto',
        'inetCidrRouteType',
        'inetCidrRouteNextHopAS',
        'updated_at',
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
