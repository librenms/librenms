<?php

namespace App\Restify;

use App\Models\OspfPort;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class OspfPortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = OspfPort::class;

    public static string $title = 'ospfIfIpAddress';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'interfaceIpAddress' => SearchableFilter::make()->setColumn('ospfIfIpAddress'),
        ];
    }

    public static function matches(): array
    {
        return [
            'ospfPortKey' => MatchFilter::make()->setType('text')->setColumn('ospf_port_id'),
            'interfaceIpAddress' => MatchFilter::make()->setType('text')->setColumn('ospfIfIpAddress'),
            'isAddressLess' => MatchFilter::make()->setType('bool')->setColumn('ospfAddressLessIf'),
            'areaId' => MatchFilter::make()->setType('text')->setColumn('ospfIfAreaId'),
            'category' => MatchFilter::make()->setType('text')->setColumn('ospfIfType'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('ospfIfAdminStat'),
            'routerPriority' => MatchFilter::make()->setType('integer')->setColumn('ospfIfRtrPriority'),
            'transitDelay' => MatchFilter::make()->setType('integer')->setColumn('ospfIfTransitDelay'),
            'retransmitInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfIfRetransInterval'),
            'helloInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfIfHelloInterval'),
            'routerDeadInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfIfRtrDeadInterval'),
            'pollInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfIfPollInterval'),
            'state' => MatchFilter::make()->setType('text')->setColumn('ospfIfState'),
            'designatedRouter' => MatchFilter::make()->setType('text')->setColumn('ospfIfDesignatedRouter'),
            'backupDesignatedRouter' => MatchFilter::make()->setType('text')->setColumn('ospfIfBackupDesignatedRouter'),
            'events' => MatchFilter::make()->setType('integer')->setColumn('ospfIfEvents'),
            'authenticationKey' => MatchFilter::make()->setType('text')->setColumn('ospfIfAuthKey'),
            'status' => MatchFilter::make()->setType('text')->setColumn('ospfIfStatus'),
            'multicastForwarding' => MatchFilter::make()->setType('text')->setColumn('ospfIfMulticastForwarding'),
            'demand' => MatchFilter::make()->setType('text')->setColumn('ospfIfDemand'),
            'authenticationCategory' => MatchFilter::make()->setType('text')->setColumn('ospfIfAuthType'),
            'metricIpAddress' => MatchFilter::make()->setType('text')->setColumn('ospfIfMetricIpAddress'),
            'metricAddressLessInterface' => MatchFilter::make()->setType('integer')->setColumn('ospfIfMetricAddressLessIf'),
            'metricTos' => MatchFilter::make()->setType('integer')->setColumn('ospfIfMetricTOS'),
            'metricValue' => MatchFilter::make()->setType('integer')->setColumn('ospfIfMetricValue'),
            'metricStatus' => MatchFilter::make()->setType('text')->setColumn('ospfIfMetricStatus'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'ospfPortKey' => SortableFilter::make()->setColumn('ospf_port_id'),
            'interfaceIpAddress' => SortableFilter::make()->setColumn('ospfIfIpAddress'),
            'isAddressLess' => SortableFilter::make()->setColumn('ospfAddressLessIf'),
            'areaId' => SortableFilter::make()->setColumn('ospfIfAreaId'),
            'category' => SortableFilter::make()->setColumn('ospfIfType'),
            'adminStatus' => SortableFilter::make()->setColumn('ospfIfAdminStat'),
            'routerPriority' => SortableFilter::make()->setColumn('ospfIfRtrPriority'),
            'transitDelay' => SortableFilter::make()->setColumn('ospfIfTransitDelay'),
            'retransmitInterval' => SortableFilter::make()->setColumn('ospfIfRetransInterval'),
            'helloInterval' => SortableFilter::make()->setColumn('ospfIfHelloInterval'),
            'routerDeadInterval' => SortableFilter::make()->setColumn('ospfIfRtrDeadInterval'),
            'pollInterval' => SortableFilter::make()->setColumn('ospfIfPollInterval'),
            'state' => SortableFilter::make()->setColumn('ospfIfState'),
            'designatedRouter' => SortableFilter::make()->setColumn('ospfIfDesignatedRouter'),
            'backupDesignatedRouter' => SortableFilter::make()->setColumn('ospfIfBackupDesignatedRouter'),
            'events' => SortableFilter::make()->setColumn('ospfIfEvents'),
            'authenticationKey' => SortableFilter::make()->setColumn('ospfIfAuthKey'),
            'status' => SortableFilter::make()->setColumn('ospfIfStatus'),
            'multicastForwarding' => SortableFilter::make()->setColumn('ospfIfMulticastForwarding'),
            'demand' => SortableFilter::make()->setColumn('ospfIfDemand'),
            'authenticationCategory' => SortableFilter::make()->setColumn('ospfIfAuthType'),
            'metricIpAddress' => SortableFilter::make()->setColumn('ospfIfMetricIpAddress'),
            'metricAddressLessInterface' => SortableFilter::make()->setColumn('ospfIfMetricAddressLessIf'),
            'metricTos' => SortableFilter::make()->setColumn('ospfIfMetricTOS'),
            'metricValue' => SortableFilter::make()->setColumn('ospfIfMetricValue'),
            'metricStatus' => SortableFilter::make()->setColumn('ospfIfMetricStatus'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('ospfPortKey', fn ($value, $model) => $model->ospf_port_id)->readonly(),
            field('interfaceIpAddress', fn ($value, $model) => $model->ospfIfIpAddress)->readonly(),
            field('isAddressLess', fn ($value, $model) => $model->ospfAddressLessIf)->readonly(),
            field('areaId', fn ($value, $model) => $model->ospfIfAreaId)->readonly(),
            field('category', fn ($value, $model) => $model->ospfIfType)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->ospfIfAdminStat)->readonly(),
            field('routerPriority', fn ($value, $model) => $model->ospfIfRtrPriority)->readonly(),
            field('transitDelay', fn ($value, $model) => $model->ospfIfTransitDelay)->readonly(),
            field('retransmitInterval', fn ($value, $model) => $model->ospfIfRetransInterval)->readonly(),
            field('helloInterval', fn ($value, $model) => $model->ospfIfHelloInterval)->readonly(),
            field('routerDeadInterval', fn ($value, $model) => $model->ospfIfRtrDeadInterval)->readonly(),
            field('pollInterval', fn ($value, $model) => $model->ospfIfPollInterval)->readonly(),
            field('state', fn ($value, $model) => $model->ospfIfState)->readonly(),
            field('designatedRouter', fn ($value, $model) => $model->ospfIfDesignatedRouter)->readonly(),
            field('backupDesignatedRouter', fn ($value, $model) => $model->ospfIfBackupDesignatedRouter)->readonly(),
            field('events', fn ($value, $model) => $model->ospfIfEvents)->readonly(),
            field('authenticationKey', fn ($value, $model) => $model->ospfIfAuthKey)->readonly(),
            field('status', fn ($value, $model) => $model->ospfIfStatus)->readonly(),
            field('multicastForwarding', fn ($value, $model) => $model->ospfIfMulticastForwarding)->readonly(),
            field('demand', fn ($value, $model) => $model->ospfIfDemand)->readonly(),
            field('authenticationCategory', fn ($value, $model) => $model->ospfIfAuthType)->readonly(),
            field('metricIpAddress', fn ($value, $model) => $model->ospfIfMetricIpAddress)->readonly(),
            field('metricAddressLessInterface', fn ($value, $model) => $model->ospfIfMetricAddressLessIf)->readonly(),
            field('metricTos', fn ($value, $model) => $model->ospfIfMetricTOS)->readonly(),
            field('metricValue', fn ($value, $model) => $model->ospfIfMetricValue)->readonly(),
            field('metricStatus', fn ($value, $model) => $model->ospfIfMetricStatus)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
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
