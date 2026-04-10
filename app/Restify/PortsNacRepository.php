<?php

namespace App\Restify;

use App\Models\PortsNac;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PortsNacRepository extends Repository
{
    public static string $model = PortsNac::class;

    public static string $id = 'ports_nac_id';

    public static string $title = 'username';

    public static array $search = [
        'username',
        'mac_address',
        'ip_address',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('auth_id')->readonly(),
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('domain')->readonly(),
            field('username')->readonly(),
            field('mac_address')->readonly(),
            field('ip_address')->readonly(),
            field('vlan')->readonly(),
            field('host_mode')->readonly(),
            field('authz_status')->readonly(),
            field('authz_by')->readonly(),
            field('authc_status')->readonly(),
            field('method')->readonly(),
            field('timeout')->readonly(),
            field('time_left')->readonly(),
            field('time_elapsed')->readonly(),
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
     * NAC entries are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * NAC entries are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
