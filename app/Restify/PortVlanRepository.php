<?php

namespace App\Restify;

use App\Models\PortVlan;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortVlanRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortVlan::class;

    public static string $id = 'port_vlan_id';

    public static string $title = 'vlan';




    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'vlan' => MatchFilter::make()->setType('integer')->setColumn('vlan'),
            'basePort' => MatchFilter::make()->setType('integer')->setColumn('baseport'),
            'priority' => MatchFilter::make()->setType('integer')->setColumn('priority'),
            'state' => MatchFilter::make()->setType('text')->setColumn('state'),
            'cost' => MatchFilter::make()->setType('integer')->setColumn('cost'),
            'isUntagged' => MatchFilter::make()->setType('bool')->setColumn('untagged'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'vlan' => SortableFilter::make()->setColumn('vlan'),
            'basePort' => SortableFilter::make()->setColumn('baseport'),
            'priority' => SortableFilter::make()->setColumn('priority'),
            'state' => SortableFilter::make()->setColumn('state'),
            'cost' => SortableFilter::make()->setColumn('cost'),
            'isUntagged' => SortableFilter::make()->setColumn('untagged'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('vlan')->readonly(),
            field('basePort', fn ($value, $model) => $model->baseport)->readonly(),
            field('priority')->readonly(),
            field('state')->readonly(),
            field('cost')->readonly(),
            field('isUntagged', fn ($value, $model) => $model->untagged)->readonly(),
        ];
    }

    /**
     * Port VLAN assignments are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port VLAN assignments are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
