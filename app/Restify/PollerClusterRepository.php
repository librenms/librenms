<?php

namespace App\Restify;

use App\Models\PollerCluster;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PollerClusterRepository extends Repository
{
    public static string $model = PollerCluster::class;

    public static string $title = 'poller_name';

    public static array $search = [
        'poller_name',
        'node_id',
    ];

    public static array $match = [
        'node_id' => 'text',
        'poller_name' => 'text',
        'poller_version' => 'text',
        'poller_groups' => 'text',
        'last_report' => 'datetime',
        'master' => 'integer',
        'poller_enabled' => 'integer',
        'poller_frequency' => 'integer',
        'poller_workers' => 'integer',
        'discovery_enabled' => 'integer',
        'discovery_frequency' => 'integer',
        'discovery_workers' => 'integer',
        'services_enabled' => 'integer',
        'alerting_enabled' => 'integer',
        'ping_enabled' => 'integer',
        'loglevel' => 'text',
    ];

    public static array $sort = [
        'node_id',
        'poller_name',
        'poller_version',
        'poller_groups',
        'last_report',
        'master',
        'poller_enabled',
        'poller_frequency',
        'poller_workers',
        'discovery_enabled',
        'discovery_frequency',
        'discovery_workers',
        'services_enabled',
        'alerting_enabled',
        'ping_enabled',
        'loglevel',
    ];

    public static function related(): array
    {
        return [
            'stats' => HasMany::make('stats', PollerClusterStatRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('node_id')->readonly(),
            field('poller_name')->readonly(),
            field('poller_version')->readonly(),
            field('poller_groups')->readonly(),
            field('last_report')->readonly(),
            field('master')->readonly(),
            field('poller_enabled')->readonly(),
            field('poller_frequency')->readonly(),
            field('poller_workers')->readonly(),
            field('discovery_enabled')->readonly(),
            field('discovery_frequency')->readonly(),
            field('discovery_workers')->readonly(),
            field('services_enabled')->readonly(),
            field('alerting_enabled')->readonly(),
            field('ping_enabled')->readonly(),
            field('loglevel')->readonly(),
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
     * Poller clusters are registered automatically when a poller node starts — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Poller clusters are managed by the poller lifecycle — they are cleaned up when a node is decommissioned.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
