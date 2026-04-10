<?php

namespace App\Restify;

use App\Models\Stp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class StpRepository extends Repository
{
    public static string $model = Stp::class;

    public static string $id = 'stp_id';

    public static string $title = 'bridgeAddress';

    public static array $search = [
        'bridgeAddress',
        'designatedRoot',
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
            field('vlan')->readonly(),
            field('rootBridge')->readonly(),
            field('bridgeAddress')->readonly(),
            field('protocolSpecification')->readonly(),
            field('priority')->readonly(),
            field('timeSinceTopologyChange')->readonly(),
            field('topChanges')->readonly(),
            field('designatedRoot')->readonly(),
            field('rootCost')->readonly(),
            field('rootPort')->readonly(),
            field('maxAge')->readonly(),
            field('helloTime')->readonly(),
            field('holdTime')->readonly(),
            field('forwardDelay')->readonly(),
            field('bridgeMaxAge')->readonly(),
            field('bridgeHelloTime')->readonly(),
            field('bridgeForwardDelay')->readonly(),
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
     * STP instances are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * STP instances are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
