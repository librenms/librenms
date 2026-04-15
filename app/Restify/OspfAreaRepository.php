<?php

namespace App\Restify;

use App\Models\OspfArea;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class OspfAreaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfArea::class;

    public static string $title = 'ospfAreaId';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'areaId' => SearchableFilter::make()->setColumn('ospfAreaId'),
        ];
    }

    public static function matches(): array
    {
        return [
            'areaId' => MatchFilter::make()->setType('text')->setColumn('ospfAreaId'),
            'authenticationCategory' => MatchFilter::make()->setType('text')->setColumn('ospfAuthType'),
            'importAsExtern' => MatchFilter::make()->setType('text')->setColumn('ospfImportAsExtern'),
            'spfRuns' => MatchFilter::make()->setType('integer')->setColumn('ospfSpfRuns'),
            'borderRouterCount' => MatchFilter::make()->setType('integer')->setColumn('ospfAreaBdrRtrCount'),
            'asBorderRouterCount' => MatchFilter::make()->setType('integer')->setColumn('ospfAsBdrRtrCount'),
            'lsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfAreaLsaCount'),
            'lsaChecksumSum' => MatchFilter::make()->setType('integer')->setColumn('ospfAreaLsaCksumSum'),
            'summary' => MatchFilter::make()->setType('text')->setColumn('ospfAreaSummary'),
            'status' => MatchFilter::make()->setType('text')->setColumn('ospfAreaStatus'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'areaId' => SortableFilter::make()->setColumn('ospfAreaId'),
            'authenticationCategory' => SortableFilter::make()->setColumn('ospfAuthType'),
            'importAsExtern' => SortableFilter::make()->setColumn('ospfImportAsExtern'),
            'spfRuns' => SortableFilter::make()->setColumn('ospfSpfRuns'),
            'borderRouterCount' => SortableFilter::make()->setColumn('ospfAreaBdrRtrCount'),
            'asBorderRouterCount' => SortableFilter::make()->setColumn('ospfAsBdrRtrCount'),
            'lsaCount' => SortableFilter::make()->setColumn('ospfAreaLsaCount'),
            'lsaChecksumSum' => SortableFilter::make()->setColumn('ospfAreaLsaCksumSum'),
            'summary' => SortableFilter::make()->setColumn('ospfAreaSummary'),
            'status' => SortableFilter::make()->setColumn('ospfAreaStatus'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('areaId', fn ($value, $model) => $model->ospfAreaId)->readonly(),
            field('authenticationCategory', fn ($value, $model) => $model->ospfAuthType)->readonly(),
            field('importAsExtern', fn ($value, $model) => $model->ospfImportAsExtern)->readonly(),
            field('spfRuns', fn ($value, $model) => $model->ospfSpfRuns)->readonly(),
            field('borderRouterCount', fn ($value, $model) => $model->ospfAreaBdrRtrCount)->readonly(),
            field('asBorderRouterCount', fn ($value, $model) => $model->ospfAsBdrRtrCount)->readonly(),
            field('lsaCount', fn ($value, $model) => $model->ospfAreaLsaCount)->readonly(),
            field('lsaChecksumSum', fn ($value, $model) => $model->ospfAreaLsaCksumSum)->readonly(),
            field('summary', fn ($value, $model) => $model->ospfAreaSummary)->readonly(),
            field('status', fn ($value, $model) => $model->ospfAreaStatus)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPF areas are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF areas are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
