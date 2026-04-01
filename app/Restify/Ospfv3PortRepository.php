<?php

namespace App\Restify;

use App\Models\Ospfv3Port;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ospfv3PortRepository extends Repository
{
    public static string $model = Ospfv3Port::class;

    public static string $title = 'ospfv3IfIndex';

    public static array $search = [
        'ospfv3IfIndex',
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
            field('ospfv3_instance_id')->readonly(),
            field('ospfv3_area_id')->readonly(),
            field('port_id')->readonly(),
            field('context_name')->readonly(),
            field('ospfv3IfIndex')->readonly(),
            field('ospfv3IfInstId')->readonly(),
            field('ospfv3IfAreaId')->readonly(),
            field('ospfv3IfType')->readonly(),
            field('ospfv3IfAdminStatus')->readonly(),
            field('ospfv3IfRtrPriority')->readonly(),
            field('ospfv3IfTransitDelay')->readonly(),
            field('ospfv3IfRetransInterval')->readonly(),
            field('ospfv3IfHelloInterval')->readonly(),
            field('ospfv3IfRtrDeadInterval')->readonly(),
            field('ospfv3IfPollInterval')->readonly(),
            field('ospfv3IfState')->readonly(),
            field('ospfv3IfDesignatedRouter')->readonly(),
            field('ospfv3IfBackupDesignatedRouter')->readonly(),
            field('ospfv3IfEvents')->readonly(),
            field('ospfv3IfDemand')->readonly(),
            field('ospfv3IfMetricValue')->readonly(),
            field('ospfv3IfLinkScopeLsaCount')->readonly(),
            field('ospfv3IfLinkLsaCksumSum')->readonly(),
            field('ospfv3IfDemandNbrProbe')->readonly(),
            field('ospfv3IfDemandNbrProbeRetransLimit')->readonly(),
            field('ospfv3IfDemandNbrProbeInterval')->readonly(),
            field('ospfv3IfTEDisabled')->readonly(),
            field('ospfv3IfLinkLSASuppression')->readonly(),
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
     * OSPFv3 ports are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 ports are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
