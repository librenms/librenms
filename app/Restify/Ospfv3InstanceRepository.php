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
