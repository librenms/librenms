<?php

namespace App\Restify;

use App\Models\Ospfv3Instance;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ospfv3InstanceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Ospfv3Instance::class;

    public static string $title = 'ospfv3RouterId';

    public static array $search = [
        'ospfv3RouterId',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'context_name' => 'text',
        'router_id' => 'text',
        'ospfv3RouterId' => 'integer',
        'ospfv3AdminStatus' => 'text',
        'ospfv3VersionNumber' => 'text',
        'ospfv3AreaBdrRtrStatus' => 'text',
        'ospfv3ASBdrRtrStatus' => 'text',
        'ospfv3AsScopeLsaCount' => 'integer',
        'ospfv3AsScopeLsaCksumSum' => 'integer',
        'ospfv3ExtLsaCount' => 'integer',
        'ospfv3OriginateNewLsas' => 'integer',
        'ospfv3RxNewLsas' => 'integer',
        'ospfv3ExtAreaLsdbLimit' => 'integer',
        'ospfv3ExitOverflowInterval' => 'integer',
        'ospfv3ReferenceBandwidth' => 'integer',
        'ospfv3RestartSupport' => 'text',
        'ospfv3RestartInterval' => 'integer',
        'ospfv3RestartStrictLsaChecking' => 'text',
        'ospfv3RestartStatus' => 'text',
        'ospfv3RestartAge' => 'integer',
        'ospfv3RestartExitReason' => 'text',
        'ospfv3StubRouterSupport' => 'text',
        'ospfv3StubRouterAdvertisement' => 'text',
        'ospfv3DiscontinuityTime' => 'integer',
        'ospfv3RestartTime' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'context_name',
        'router_id',
        'ospfv3RouterId',
        'ospfv3AdminStatus',
        'ospfv3VersionNumber',
        'ospfv3AreaBdrRtrStatus',
        'ospfv3ASBdrRtrStatus',
        'ospfv3AsScopeLsaCount',
        'ospfv3AsScopeLsaCksumSum',
        'ospfv3ExtLsaCount',
        'ospfv3OriginateNewLsas',
        'ospfv3RxNewLsas',
        'ospfv3ExtAreaLsdbLimit',
        'ospfv3ExitOverflowInterval',
        'ospfv3ReferenceBandwidth',
        'ospfv3RestartSupport',
        'ospfv3RestartInterval',
        'ospfv3RestartStrictLsaChecking',
        'ospfv3RestartStatus',
        'ospfv3RestartAge',
        'ospfv3RestartExitReason',
        'ospfv3StubRouterSupport',
        'ospfv3StubRouterAdvertisement',
        'ospfv3DiscontinuityTime',
        'ospfv3RestartTime',
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
            field('context_name')->readonly(),
            field('router_id')->readonly(),
            field('ospfv3RouterId')->readonly(),
            field('ospfv3AdminStatus')->readonly(),
            field('ospfv3VersionNumber')->readonly(),
            field('ospfv3AreaBdrRtrStatus')->readonly(),
            field('ospfv3ASBdrRtrStatus')->readonly(),
            field('ospfv3AsScopeLsaCount')->readonly(),
            field('ospfv3AsScopeLsaCksumSum')->readonly(),
            field('ospfv3ExtLsaCount')->readonly(),
            field('ospfv3OriginateNewLsas')->readonly(),
            field('ospfv3RxNewLsas')->readonly(),
            field('ospfv3ExtAreaLsdbLimit')->readonly(),
            field('ospfv3ExitOverflowInterval')->readonly(),
            field('ospfv3ReferenceBandwidth')->readonly(),
            field('ospfv3RestartSupport')->readonly(),
            field('ospfv3RestartInterval')->readonly(),
            field('ospfv3RestartStrictLsaChecking')->readonly(),
            field('ospfv3RestartStatus')->readonly(),
            field('ospfv3RestartAge')->readonly(),
            field('ospfv3RestartExitReason')->readonly(),
            field('ospfv3StubRouterSupport')->readonly(),
            field('ospfv3StubRouterAdvertisement')->readonly(),
            field('ospfv3DiscontinuityTime')->readonly(),
            field('ospfv3RestartTime')->readonly(),
        ];
    }

    /**
     * OSPFv3 instances are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 instances are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
