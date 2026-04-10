<?php

namespace App\Restify;

use App\Models\IpsecTunnel;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class IpsecTunnelRepository extends Repository
{
    public static string $model = IpsecTunnel::class;

    public static string $id = 'tunnel_id';

    public static string $title = 'tunnel_name';

    public static array $search = [
        'tunnel_name',
        'peer_addr',
        'local_addr',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('peer_port')->readonly(),
            field('peer_addr')->readonly(),
            field('local_addr')->readonly(),
            field('local_port')->readonly(),
            field('tunnel_name')->readonly(),
            field('tunnel_status')->readonly(),
        ];
    }

    /**
     * IpsecTunnel extends plain Model (no hasAccess scope), so we filter by device access on device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', IpsecTunnel::class)) {
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
     * IPsec tunnels are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPsec tunnels are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
