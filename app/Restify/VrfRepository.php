<?php

namespace App\Restify;

use App\Models\Vrf;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class VrfRepository extends Repository
{
    public static string $model = Vrf::class;

    /**
     * Override Restify's default pluralization which generates "vrves" instead of "vrfs".
     */
    public static string $uriKey = 'vrfs';

    public static string $id = 'vrf_id';

    public static string $title = 'vrf_name';

    public static array $search = [
        'vrf_name',
        'vrf_oid',
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
            field('vrf_oid')->readonly(),
            field('vrf_name')->readonly(),
            field('bgpLocalAs')->readonly(),
            field('mplsVpnVrfRouteDistinguisher')->readonly(),
            field('mplsVpnVrfDescription')->readonly(),
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
     * VRFs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VRFs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
