<?php

namespace App\Restify;

use App\Models\Ipv6Nd;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Ipv6NdRepository extends Repository
{
    public static string $model = Ipv6Nd::class;

    public static string $title = 'ipv6_address';

    public static array $search = [
        'ipv6_address',
        'mac_address',
    ];

    public static array $match = [
        'port_id' => 'integer',
        'device_id' => 'integer',
        'mac_address' => 'text',
        'ipv6_address' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'port_id',
        'device_id',
        'mac_address',
        'ipv6_address',
        'context_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('port_id')->readonly(),
            field('device_id')->readonly(),
            field('mac_address')->readonly(),
            field('ipv6_address')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    /**
     * Ipv6Nd extends plain Model (no hasAccess scope), so we filter by device access on device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', Ipv6Nd::class)) {
                return $query;
            }

            return $query->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * IPv6 neighbor discovery entries are populated automatically by LibreNMS during polling — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv6 neighbor discovery entries are managed by the LibreNMS polling process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
