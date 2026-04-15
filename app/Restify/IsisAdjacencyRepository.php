<?php

namespace App\Restify;

use App\Models\IsisAdjacency;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class IsisAdjacencyRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = IsisAdjacency::class;

    public static string $title = 'isisISAdjNeighSysID';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'neighborSystemId' => SearchableFilter::make()->setColumn('isisISAdjNeighSysID'),
        ];
    }

    public static function matches(): array
    {
        return [
            'interfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('ifIndex'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('index'),
            'state' => MatchFilter::make()->setType('text')->setColumn('isisISAdjState'),
            'neighborSystemCategory' => MatchFilter::make()->setType('text')->setColumn('isisISAdjNeighSysType'),
            'neighborSystemId' => MatchFilter::make()->setType('text')->setColumn('isisISAdjNeighSysID'),
            'neighborPriority' => MatchFilter::make()->setType('integer')->setColumn('isisISAdjNeighPriority'),
            'lastUpAt' => MatchFilter::make()->setType('datetime')->setColumn('isisISAdjLastUpTime'),
            'areaAddress' => MatchFilter::make()->setType('text')->setColumn('isisISAdjAreaAddress'),
            'ipAddressCategory' => MatchFilter::make()->setType('text')->setColumn('isisISAdjIPAddrType'),
            'ipAddress' => MatchFilter::make()->setType('text')->setColumn('isisISAdjIPAddrAddress'),
            'circuitAdminState' => MatchFilter::make()->setType('text')->setColumn('isisCircAdminState'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'interfaceIndex' => SortableFilter::make()->setColumn('ifIndex'),
            'index' => SortableFilter::make()->setColumn('index'),
            'state' => SortableFilter::make()->setColumn('isisISAdjState'),
            'neighborSystemCategory' => SortableFilter::make()->setColumn('isisISAdjNeighSysType'),
            'neighborSystemId' => SortableFilter::make()->setColumn('isisISAdjNeighSysID'),
            'neighborPriority' => SortableFilter::make()->setColumn('isisISAdjNeighPriority'),
            'lastUpAt' => SortableFilter::make()->setColumn('isisISAdjLastUpTime'),
            'areaAddress' => SortableFilter::make()->setColumn('isisISAdjAreaAddress'),
            'ipAddressCategory' => SortableFilter::make()->setColumn('isisISAdjIPAddrType'),
            'ipAddress' => SortableFilter::make()->setColumn('isisISAdjIPAddrAddress'),
            'circuitAdminState' => SortableFilter::make()->setColumn('isisCircAdminState'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('interfaceIndex', fn ($value, $model) => $model->ifIndex)->readonly(),
            field('index')->readonly(),
            field('state', fn ($value, $model) => $model->isisISAdjState)->readonly(),
            field('neighborSystemCategory', fn ($value, $model) => $model->isisISAdjNeighSysType)->readonly(),
            field('neighborSystemId', fn ($value, $model) => $model->isisISAdjNeighSysID)->readonly(),
            field('neighborPriority', fn ($value, $model) => $model->isisISAdjNeighPriority)->readonly(),
            field('lastUpAt', fn ($value, $model) => $model->isisISAdjLastUpTime)->readonly(),
            field('areaAddress', fn ($value, $model) => $model->isisISAdjAreaAddress)->readonly(),
            field('ipAddressCategory', fn ($value, $model) => $model->isisISAdjIPAddrType)->readonly(),
            field('ipAddress', fn ($value, $model) => $model->isisISAdjIPAddrAddress)->readonly(),
            field('circuitAdminState', fn ($value, $model) => $model->isisCircAdminState)->readonly(),
        ];
    }

    /**
     * ISIS adjacencies are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * ISIS adjacencies are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
