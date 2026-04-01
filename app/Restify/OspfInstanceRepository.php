<?php

namespace App\Restify;

use App\Models\OspfInstance;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class OspfInstanceRepository extends Repository
{
    public static string $model = OspfInstance::class;

    public static string $title = 'ospfRouterId';

    public static array $search = [
        'ospfRouterId',
        'context_name',
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
            field('ospf_instance_id')->readonly(),
            field('ospfRouterId')->readonly(),
            field('ospfAdminStat')->readonly(),
            field('ospfVersionNumber')->readonly(),
            field('ospfAreaBdrRtrStatus')->readonly(),
            field('ospfASBdrRtrStatus')->readonly(),
            field('ospfExternLsaCount')->readonly(),
            field('ospfExternLsaCksumSum')->readonly(),
            field('ospfTOSSupport')->readonly(),
            field('ospfOriginateNewLsas')->readonly(),
            field('ospfRxNewLsas')->readonly(),
            field('ospfExtLsdbLimit')->readonly(),
            field('ospfMulticastExtensions')->readonly(),
            field('ospfExitOverflowInterval')->readonly(),
            field('ospfDemandExtensions')->readonly(),
            field('context_name')->readonly(),
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
     * OSPF instances are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF instances are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
