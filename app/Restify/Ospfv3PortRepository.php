<?php

namespace App\Restify;

use App\Models\Ospfv3Port;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ospfv3PortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ospfv3Port::class;

    public static string $title = 'ospfv3IfIndex';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'interfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfIndex'),
            'instanceId' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfInstId'),
            'areaId' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfAreaId'),
            'category' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfType'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfAdminStatus'),
            'routerPriority' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfRtrPriority'),
            'transitDelay' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfTransitDelay'),
            'retransmitInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfRetransInterval'),
            'helloInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfHelloInterval'),
            'routerDeadInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfRtrDeadInterval'),
            'pollInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfPollInterval'),
            'state' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfState'),
            'designatedRouter' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfDesignatedRouter'),
            'backupDesignatedRouter' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfBackupDesignatedRouter'),
            'events' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfEvents'),
            'demand' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfDemand'),
            'metricValue' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfMetricValue'),
            'linkScopeLsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfLinkScopeLsaCount'),
            'linkLsaChecksumSum' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfLinkLsaCksumSum'),
            'demandNeighborProbe' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfDemandNbrProbe'),
            'demandNeighborProbeRetransmitLimit' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfDemandNbrProbeRetransLimit'),
            'demandNeighborProbeInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3IfDemandNbrProbeInterval'),
            'isTrafficEngineeringEnabled' => MatchFilter::make()->setType('bool')->setColumn('ospfv3IfTEDisabled'),
            'linkLsaSuppression' => MatchFilter::make()->setType('text')->setColumn('ospfv3IfLinkLSASuppression'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'interfaceIndex' => SortableFilter::make()->setColumn('ospfv3IfIndex'),
            'instanceId' => SortableFilter::make()->setColumn('ospfv3IfInstId'),
            'areaId' => SortableFilter::make()->setColumn('ospfv3IfAreaId'),
            'category' => SortableFilter::make()->setColumn('ospfv3IfType'),
            'adminStatus' => SortableFilter::make()->setColumn('ospfv3IfAdminStatus'),
            'routerPriority' => SortableFilter::make()->setColumn('ospfv3IfRtrPriority'),
            'transitDelay' => SortableFilter::make()->setColumn('ospfv3IfTransitDelay'),
            'retransmitInterval' => SortableFilter::make()->setColumn('ospfv3IfRetransInterval'),
            'helloInterval' => SortableFilter::make()->setColumn('ospfv3IfHelloInterval'),
            'routerDeadInterval' => SortableFilter::make()->setColumn('ospfv3IfRtrDeadInterval'),
            'pollInterval' => SortableFilter::make()->setColumn('ospfv3IfPollInterval'),
            'state' => SortableFilter::make()->setColumn('ospfv3IfState'),
            'designatedRouter' => SortableFilter::make()->setColumn('ospfv3IfDesignatedRouter'),
            'backupDesignatedRouter' => SortableFilter::make()->setColumn('ospfv3IfBackupDesignatedRouter'),
            'events' => SortableFilter::make()->setColumn('ospfv3IfEvents'),
            'demand' => SortableFilter::make()->setColumn('ospfv3IfDemand'),
            'metricValue' => SortableFilter::make()->setColumn('ospfv3IfMetricValue'),
            'linkScopeLsaCount' => SortableFilter::make()->setColumn('ospfv3IfLinkScopeLsaCount'),
            'linkLsaChecksumSum' => SortableFilter::make()->setColumn('ospfv3IfLinkLsaCksumSum'),
            'demandNeighborProbe' => SortableFilter::make()->setColumn('ospfv3IfDemandNbrProbe'),
            'demandNeighborProbeRetransmitLimit' => SortableFilter::make()->setColumn('ospfv3IfDemandNbrProbeRetransLimit'),
            'demandNeighborProbeInterval' => SortableFilter::make()->setColumn('ospfv3IfDemandNbrProbeInterval'),
            'isTrafficEngineeringEnabled' => SortableFilter::make()->setColumn('ospfv3IfTEDisabled'),
            'linkLsaSuppression' => SortableFilter::make()->setColumn('ospfv3IfLinkLSASuppression'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('interfaceIndex', fn ($value, $model) => $model->ospfv3IfIndex)->readonly(),
            field('instanceId', fn ($value, $model) => $model->ospfv3IfInstId)->readonly(),
            field('areaId', fn ($value, $model) => $model->ospfv3IfAreaId)->readonly(),
            field('category', fn ($value, $model) => $model->ospfv3IfType)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->ospfv3IfAdminStatus)->readonly(),
            field('routerPriority', fn ($value, $model) => $model->ospfv3IfRtrPriority)->readonly(),
            field('transitDelay', fn ($value, $model) => $model->ospfv3IfTransitDelay)->readonly(),
            field('retransmitInterval', fn ($value, $model) => $model->ospfv3IfRetransInterval)->readonly(),
            field('helloInterval', fn ($value, $model) => $model->ospfv3IfHelloInterval)->readonly(),
            field('routerDeadInterval', fn ($value, $model) => $model->ospfv3IfRtrDeadInterval)->readonly(),
            field('pollInterval', fn ($value, $model) => $model->ospfv3IfPollInterval)->readonly(),
            field('state', fn ($value, $model) => $model->ospfv3IfState)->readonly(),
            field('designatedRouter', fn ($value, $model) => $model->ospfv3IfDesignatedRouter)->readonly(),
            field('backupDesignatedRouter', fn ($value, $model) => $model->ospfv3IfBackupDesignatedRouter)->readonly(),
            field('events', fn ($value, $model) => $model->ospfv3IfEvents)->readonly(),
            field('demand', fn ($value, $model) => $model->ospfv3IfDemand)->readonly(),
            field('metricValue', fn ($value, $model) => $model->ospfv3IfMetricValue)->readonly(),
            field('linkScopeLsaCount', fn ($value, $model) => $model->ospfv3IfLinkScopeLsaCount)->readonly(),
            field('linkLsaChecksumSum', fn ($value, $model) => $model->ospfv3IfLinkLsaCksumSum)->readonly(),
            field('demandNeighborProbe', fn ($value, $model) => $model->ospfv3IfDemandNbrProbe)->readonly(),
            field('demandNeighborProbeRetransmitLimit', fn ($value, $model) => $model->ospfv3IfDemandNbrProbeRetransLimit)->readonly(),
            field('demandNeighborProbeInterval', fn ($value, $model) => $model->ospfv3IfDemandNbrProbeInterval)->readonly(),
            field('isTrafficEngineeringEnabled', fn ($value, $model) => ! $model->ospfv3IfTEDisabled)->readonly(),
            field('linkLsaSuppression', fn ($value, $model) => $model->ospfv3IfLinkLSASuppression)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
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
