<?php

namespace App\Restify;

use App\Models\Ospfv3Port;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ospfv3PortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ospfv3Port::class;

    public static string $title = 'ospfv3IfIndex';

    public static array $search = [
        'ospfv3IfIndex',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'ospfv3_instance_id' => 'integer',
        'ospfv3_area_id' => 'integer',
        'port_id' => 'integer',
        'context_name' => 'text',
        'ospfv3IfIndex' => 'integer',
        'ospfv3IfInstId' => 'integer',
        'ospfv3IfAreaId' => 'integer',
        'ospfv3IfType' => 'text',
        'ospfv3IfAdminStatus' => 'text',
        'ospfv3IfRtrPriority' => 'integer',
        'ospfv3IfTransitDelay' => 'integer',
        'ospfv3IfRetransInterval' => 'integer',
        'ospfv3IfHelloInterval' => 'integer',
        'ospfv3IfRtrDeadInterval' => 'integer',
        'ospfv3IfPollInterval' => 'integer',
        'ospfv3IfState' => 'text',
        'ospfv3IfDesignatedRouter' => 'text',
        'ospfv3IfBackupDesignatedRouter' => 'text',
        'ospfv3IfEvents' => 'integer',
        'ospfv3IfDemand' => 'text',
        'ospfv3IfMetricValue' => 'integer',
        'ospfv3IfLinkScopeLsaCount' => 'integer',
        'ospfv3IfLinkLsaCksumSum' => 'integer',
        'ospfv3IfDemandNbrProbe' => 'text',
        'ospfv3IfDemandNbrProbeRetransLimit' => 'integer',
        'ospfv3IfDemandNbrProbeInterval' => 'integer',
        'ospfv3IfTEDisabled' => 'text',
        'ospfv3IfLinkLSASuppression' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'ospfv3_instance_id',
        'ospfv3_area_id',
        'port_id',
        'context_name',
        'ospfv3IfIndex',
        'ospfv3IfInstId',
        'ospfv3IfAreaId',
        'ospfv3IfType',
        'ospfv3IfAdminStatus',
        'ospfv3IfRtrPriority',
        'ospfv3IfTransitDelay',
        'ospfv3IfRetransInterval',
        'ospfv3IfHelloInterval',
        'ospfv3IfRtrDeadInterval',
        'ospfv3IfPollInterval',
        'ospfv3IfState',
        'ospfv3IfDesignatedRouter',
        'ospfv3IfBackupDesignatedRouter',
        'ospfv3IfEvents',
        'ospfv3IfDemand',
        'ospfv3IfMetricValue',
        'ospfv3IfLinkScopeLsaCount',
        'ospfv3IfLinkLsaCksumSum',
        'ospfv3IfDemandNbrProbe',
        'ospfv3IfDemandNbrProbeRetransLimit',
        'ospfv3IfDemandNbrProbeInterval',
        'ospfv3IfTEDisabled',
        'ospfv3IfLinkLSASuppression',
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
