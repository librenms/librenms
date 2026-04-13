<?php

namespace App\Restify;

use App\Models\OspfInstance;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class OspfInstanceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfInstance::class;

    public static string $title = 'ospfRouterId';

    public static array $search = [
        'ospfRouterId',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'ospf_instance_id' => 'integer',
        'ospfRouterId' => 'text',
        'ospfAdminStat' => 'text',
        'ospfVersionNumber' => 'text',
        'ospfAreaBdrRtrStatus' => 'text',
        'ospfASBdrRtrStatus' => 'text',
        'ospfExternLsaCount' => 'integer',
        'ospfExternLsaCksumSum' => 'integer',
        'ospfTOSSupport' => 'text',
        'ospfOriginateNewLsas' => 'integer',
        'ospfRxNewLsas' => 'integer',
        'ospfExtLsdbLimit' => 'integer',
        'ospfMulticastExtensions' => 'integer',
        'ospfExitOverflowInterval' => 'integer',
        'ospfDemandExtensions' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'ospf_instance_id',
        'ospfRouterId',
        'ospfAdminStat',
        'ospfVersionNumber',
        'ospfAreaBdrRtrStatus',
        'ospfASBdrRtrStatus',
        'ospfExternLsaCount',
        'ospfExternLsaCksumSum',
        'ospfTOSSupport',
        'ospfOriginateNewLsas',
        'ospfRxNewLsas',
        'ospfExtLsdbLimit',
        'ospfMulticastExtensions',
        'ospfExitOverflowInterval',
        'ospfDemandExtensions',
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
