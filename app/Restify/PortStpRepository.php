<?php

namespace App\Restify;

use App\Models\PortStp;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PortStpRepository extends Repository
{
    public static string $model = PortStp::class;

    public static string $id = 'port_stp_id';

    public static string $title = 'state';

    public static array $search = [
        'state',
        'designatedRoot',
        'designatedBridge',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('vlan')->readonly(),
            field('port_id')->readonly(),
            field('port_index')->readonly(),
            field('priority')->readonly(),
            field('state')->readonly(),
            field('enable')->readonly(),
            field('pathCost')->readonly(),
            field('designatedRoot')->readonly(),
            field('designatedCost')->readonly(),
            field('designatedBridge')->readonly(),
            field('designatedPort')->readonly(),
            field('forwardTransitions')->readonly(),
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
     * Port STP entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port STP entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
