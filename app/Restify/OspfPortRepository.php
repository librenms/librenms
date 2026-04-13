<?php

namespace App\Restify;

use App\Models\OspfPort;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class OspfPortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = OspfPort::class;

    public static string $title = 'ospfIfIpAddress';

    public static array $search = [
        'ospfIfIpAddress',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'port_id' => 'integer',
        'ospf_port_id' => 'text',
        'ospfIfIpAddress' => 'text',
        'ospfAddressLessIf' => 'integer',
        'ospfIfAreaId' => 'text',
        'ospfIfType' => 'text',
        'ospfIfAdminStat' => 'text',
        'ospfIfRtrPriority' => 'integer',
        'ospfIfTransitDelay' => 'integer',
        'ospfIfRetransInterval' => 'integer',
        'ospfIfHelloInterval' => 'integer',
        'ospfIfRtrDeadInterval' => 'integer',
        'ospfIfPollInterval' => 'integer',
        'ospfIfState' => 'text',
        'ospfIfDesignatedRouter' => 'text',
        'ospfIfBackupDesignatedRouter' => 'text',
        'ospfIfEvents' => 'integer',
        'ospfIfAuthKey' => 'text',
        'ospfIfStatus' => 'text',
        'ospfIfMulticastForwarding' => 'text',
        'ospfIfDemand' => 'text',
        'ospfIfAuthType' => 'text',
        'ospfIfMetricIpAddress' => 'text',
        'ospfIfMetricAddressLessIf' => 'integer',
        'ospfIfMetricTOS' => 'integer',
        'ospfIfMetricValue' => 'integer',
        'ospfIfMetricStatus' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'port_id',
        'ospf_port_id',
        'ospfIfIpAddress',
        'ospfAddressLessIf',
        'ospfIfAreaId',
        'ospfIfType',
        'ospfIfAdminStat',
        'ospfIfRtrPriority',
        'ospfIfTransitDelay',
        'ospfIfRetransInterval',
        'ospfIfHelloInterval',
        'ospfIfRtrDeadInterval',
        'ospfIfPollInterval',
        'ospfIfState',
        'ospfIfDesignatedRouter',
        'ospfIfBackupDesignatedRouter',
        'ospfIfEvents',
        'ospfIfAuthKey',
        'ospfIfStatus',
        'ospfIfMulticastForwarding',
        'ospfIfDemand',
        'ospfIfAuthType',
        'ospfIfMetricIpAddress',
        'ospfIfMetricAddressLessIf',
        'ospfIfMetricTOS',
        'ospfIfMetricValue',
        'ospfIfMetricStatus',
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
            field('port_id')->readonly(),
            field('ospf_port_id')->readonly(),
            field('ospfIfIpAddress')->readonly(),
            field('ospfAddressLessIf')->readonly(),
            field('ospfIfAreaId')->readonly(),
            field('ospfIfType')->readonly(),
            field('ospfIfAdminStat')->readonly(),
            field('ospfIfRtrPriority')->readonly(),
            field('ospfIfTransitDelay')->readonly(),
            field('ospfIfRetransInterval')->readonly(),
            field('ospfIfHelloInterval')->readonly(),
            field('ospfIfRtrDeadInterval')->readonly(),
            field('ospfIfPollInterval')->readonly(),
            field('ospfIfState')->readonly(),
            field('ospfIfDesignatedRouter')->readonly(),
            field('ospfIfBackupDesignatedRouter')->readonly(),
            field('ospfIfEvents')->readonly(),
            field('ospfIfAuthKey')->readonly(),
            field('ospfIfStatus')->readonly(),
            field('ospfIfMulticastForwarding')->readonly(),
            field('ospfIfDemand')->readonly(),
            field('ospfIfAuthType')->readonly(),
            field('ospfIfMetricIpAddress')->readonly(),
            field('ospfIfMetricAddressLessIf')->readonly(),
            field('ospfIfMetricTOS')->readonly(),
            field('ospfIfMetricValue')->readonly(),
            field('ospfIfMetricStatus')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    /**
     * OSPF ports are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF ports are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
