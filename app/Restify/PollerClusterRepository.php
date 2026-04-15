<?php

namespace App\Restify;

use App\Models\PollerCluster;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PollerClusterRepository extends Repository
{
    public static string $model = PollerCluster::class;

    public static string $title = 'poller_name';




    public static function related(): array
    {
        return [
            'stats' => HasMany::make('stats', PollerClusterStatRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('poller_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'nodeId' => MatchFilter::make()->setType('text')->setColumn('node_id'),
            'name' => MatchFilter::make()->setType('text')->setColumn('poller_name'),
            'version' => MatchFilter::make()->setType('text')->setColumn('poller_version'),
            'groups' => MatchFilter::make()->setType('text')->setColumn('poller_groups'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('last_report'),
            'isMaster' => MatchFilter::make()->setType('bool')->setColumn('master'),
            'isPollerEnabled' => MatchFilter::make()->setType('bool')->setColumn('poller_enabled'),
            'pollerFrequency' => MatchFilter::make()->setType('integer')->setColumn('poller_frequency'),
            'pollerWorkers' => MatchFilter::make()->setType('integer')->setColumn('poller_workers'),
            'isDiscoveryEnabled' => MatchFilter::make()->setType('bool')->setColumn('discovery_enabled'),
            'discoveryFrequency' => MatchFilter::make()->setType('integer')->setColumn('discovery_frequency'),
            'discoveryWorkers' => MatchFilter::make()->setType('integer')->setColumn('discovery_workers'),
            'areServicesEnabled' => MatchFilter::make()->setType('bool')->setColumn('services_enabled'),
            'isAlertingEnabled' => MatchFilter::make()->setType('bool')->setColumn('alerting_enabled'),
            'isPingEnabled' => MatchFilter::make()->setType('bool')->setColumn('ping_enabled'),
            'logLevel' => MatchFilter::make()->setType('text')->setColumn('loglevel'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'nodeId' => SortableFilter::make()->setColumn('node_id'),
            'name' => SortableFilter::make()->setColumn('poller_name'),
            'version' => SortableFilter::make()->setColumn('poller_version'),
            'groups' => SortableFilter::make()->setColumn('poller_groups'),
            'updatedAt' => SortableFilter::make()->setColumn('last_report'),
            'isMaster' => SortableFilter::make()->setColumn('master'),
            'isPollerEnabled' => SortableFilter::make()->setColumn('poller_enabled'),
            'pollerFrequency' => SortableFilter::make()->setColumn('poller_frequency'),
            'pollerWorkers' => SortableFilter::make()->setColumn('poller_workers'),
            'isDiscoveryEnabled' => SortableFilter::make()->setColumn('discovery_enabled'),
            'discoveryFrequency' => SortableFilter::make()->setColumn('discovery_frequency'),
            'discoveryWorkers' => SortableFilter::make()->setColumn('discovery_workers'),
            'areServicesEnabled' => SortableFilter::make()->setColumn('services_enabled'),
            'isAlertingEnabled' => SortableFilter::make()->setColumn('alerting_enabled'),
            'isPingEnabled' => SortableFilter::make()->setColumn('ping_enabled'),
            'logLevel' => SortableFilter::make()->setColumn('loglevel'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('nodeId', fn ($value, $model) => $model->node_id)->readonly(),
            field('name', fn ($value, $model) => $model->poller_name)->readonly(),
            field('version', fn ($value, $model) => $model->poller_version)->readonly(),
            field('groups', fn ($value, $model) => $model->poller_groups)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->last_report)->readonly(),
            field('isMaster', fn ($value, $model) => $model->master)->readonly(),
            field('isPollerEnabled', fn ($value, $model) => $model->poller_enabled)->readonly(),
            field('pollerFrequency', fn ($value, $model) => $model->poller_frequency)->readonly(),
            field('pollerWorkers', fn ($value, $model) => $model->poller_workers)->readonly(),
            field('isDiscoveryEnabled', fn ($value, $model) => $model->discovery_enabled)->readonly(),
            field('discoveryFrequency', fn ($value, $model) => $model->discovery_frequency)->readonly(),
            field('discoveryWorkers', fn ($value, $model) => $model->discovery_workers)->readonly(),
            field('areServicesEnabled', fn ($value, $model) => $model->services_enabled)->readonly(),
            field('isAlertingEnabled', fn ($value, $model) => $model->alerting_enabled)->readonly(),
            field('isPingEnabled', fn ($value, $model) => $model->ping_enabled)->readonly(),
            field('logLevel', fn ($value, $model) => $model->loglevel)->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    /**
     * Poller clusters are registered automatically when a poller node starts not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Poller clusters are managed by the poller lifecycle they are cleaned up when a node is decommissioned.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
