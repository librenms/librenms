<?php

namespace App\Restify;

use App\Models\Stp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class StpRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Stp::class;

    public static string $id = 'stp_id';

    public static string $title = 'bridgeAddress';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'rootBridge' => SearchableFilter::make()->setColumn('rootBridge'),
            'bridgeAddress' => SearchableFilter::make()->setColumn('bridgeAddress'),
        ];
    }

    public static function matches(): array
    {
        return [
            'vlan' => MatchFilter::make()->setType('integer')->setColumn('vlan'),
            'rootBridge' => MatchFilter::make()->setType('text')->setColumn('rootBridge'),
            'bridgeAddress' => MatchFilter::make()->setType('text')->setColumn('bridgeAddress'),
            'protocolSpecification' => MatchFilter::make()->setType('integer')->setColumn('protocolSpecification'),
            'priority' => MatchFilter::make()->setType('integer')->setColumn('priority'),
            'timeSinceTopologyChange' => MatchFilter::make()->setType('integer')->setColumn('timeSinceTopologyChange'),
            'topologyChanges' => MatchFilter::make()->setType('integer')->setColumn('topChanges'),
            'designatedRoot' => MatchFilter::make()->setType('text')->setColumn('designatedRoot'),
            'rootCost' => MatchFilter::make()->setType('integer')->setColumn('rootCost'),
            'rootPort' => MatchFilter::make()->setType('integer')->setColumn('rootPort'),
            'maxAge' => MatchFilter::make()->setType('integer')->setColumn('maxAge'),
            'helloTime' => MatchFilter::make()->setType('integer')->setColumn('helloTime'),
            'holdTime' => MatchFilter::make()->setType('integer')->setColumn('holdTime'),
            'forwardDelay' => MatchFilter::make()->setType('integer')->setColumn('forwardDelay'),
            'bridgeMaxAge' => MatchFilter::make()->setType('integer')->setColumn('bridgeMaxAge'),
            'bridgeHelloTime' => MatchFilter::make()->setType('integer')->setColumn('bridgeHelloTime'),
            'bridgeForwardDelay' => MatchFilter::make()->setType('integer')->setColumn('bridgeForwardDelay'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'vlan' => SortableFilter::make()->setColumn('vlan'),
            'rootBridge' => SortableFilter::make()->setColumn('rootBridge'),
            'bridgeAddress' => SortableFilter::make()->setColumn('bridgeAddress'),
            'protocolSpecification' => SortableFilter::make()->setColumn('protocolSpecification'),
            'priority' => SortableFilter::make()->setColumn('priority'),
            'timeSinceTopologyChange' => SortableFilter::make()->setColumn('timeSinceTopologyChange'),
            'topologyChanges' => SortableFilter::make()->setColumn('topChanges'),
            'designatedRoot' => SortableFilter::make()->setColumn('designatedRoot'),
            'rootCost' => SortableFilter::make()->setColumn('rootCost'),
            'rootPort' => SortableFilter::make()->setColumn('rootPort'),
            'maxAge' => SortableFilter::make()->setColumn('maxAge'),
            'helloTime' => SortableFilter::make()->setColumn('helloTime'),
            'holdTime' => SortableFilter::make()->setColumn('holdTime'),
            'forwardDelay' => SortableFilter::make()->setColumn('forwardDelay'),
            'bridgeMaxAge' => SortableFilter::make()->setColumn('bridgeMaxAge'),
            'bridgeHelloTime' => SortableFilter::make()->setColumn('bridgeHelloTime'),
            'bridgeForwardDelay' => SortableFilter::make()->setColumn('bridgeForwardDelay'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('vlan')->readonly(),
            field('rootBridge')->readonly(),
            field('bridgeAddress')->readonly(),
            field('protocolSpecification')->readonly(),
            field('priority')->readonly(),
            field('timeSinceTopologyChange')->readonly(),
            field('topologyChanges', fn ($value, $model) => $model->topChanges)->readonly(),
            field('designatedRoot')->readonly(),
            field('rootCost')->readonly(),
            field('rootPort')->readonly(),
            field('maxAge')->readonly(),
            field('helloTime')->readonly(),
            field('holdTime')->readonly(),
            field('forwardDelay')->readonly(),
            field('bridgeMaxAge')->readonly(),
            field('bridgeHelloTime')->readonly(),
            field('bridgeForwardDelay')->readonly(),
        ];
    }

    /**
     * STP instances are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * STP instances are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
