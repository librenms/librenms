<?php

namespace App\Restify;

use App\Models\PortStp;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortStpRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortStp::class;

    public static string $uriKey = 'port-spanning-trees';

    public static string $id = 'port_stp_id';

    public static string $title = 'state';




    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'vlan' => MatchFilter::make()->setType('integer')->setColumn('vlan'),
            'portIndex' => MatchFilter::make()->setType('integer')->setColumn('port_index'),
            'priority' => MatchFilter::make()->setType('integer')->setColumn('priority'),
            'state' => MatchFilter::make()->setType('text')->setColumn('state'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('enable'),
            'pathCost' => MatchFilter::make()->setType('integer')->setColumn('pathCost'),
            'designatedRoot' => MatchFilter::make()->setType('text')->setColumn('designatedRoot'),
            'designatedCost' => MatchFilter::make()->setType('integer')->setColumn('designatedCost'),
            'designatedBridge' => MatchFilter::make()->setType('text')->setColumn('designatedBridge'),
            'designatedPort' => MatchFilter::make()->setType('text')->setColumn('designatedPort'),
            'forwardTransitions' => MatchFilter::make()->setType('integer')->setColumn('forwardTransitions'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'vlan' => SortableFilter::make()->setColumn('vlan'),
            'portIndex' => SortableFilter::make()->setColumn('port_index'),
            'priority' => SortableFilter::make()->setColumn('priority'),
            'state' => SortableFilter::make()->setColumn('state'),
            'isEnabled' => SortableFilter::make()->setColumn('enable'),
            'pathCost' => SortableFilter::make()->setColumn('pathCost'),
            'designatedRoot' => SortableFilter::make()->setColumn('designatedRoot'),
            'designatedCost' => SortableFilter::make()->setColumn('designatedCost'),
            'designatedBridge' => SortableFilter::make()->setColumn('designatedBridge'),
            'designatedPort' => SortableFilter::make()->setColumn('designatedPort'),
            'forwardTransitions' => SortableFilter::make()->setColumn('forwardTransitions'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('vlan')->readonly(),
            field('portIndex', fn ($value, $model) => $model->port_index)->readonly(),
            field('priority')->readonly(),
            field('state')->readonly(),
            field('isEnabled', fn ($value, $model) => $model->enable)->readonly(),
            field('pathCost')->readonly(),
            field('designatedRoot')->readonly(),
            field('designatedCost')->readonly(),
            field('designatedBridge')->readonly(),
            field('designatedPort')->readonly(),
            field('forwardTransitions')->readonly(),
        ];
    }

    /**
     * Port STP entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port STP entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
