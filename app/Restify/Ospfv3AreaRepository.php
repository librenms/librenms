<?php

namespace App\Restify;

use App\Models\Ospfv3Area;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ospfv3AreaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Ospfv3Area::class;

    public static string $title = 'ospfv3AreaId';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'areaId' => SearchableFilter::make()->setColumn('ospfv3AreaId'),
        ];
    }

    public static function matches(): array
    {
        return [
            'areaId' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaId'),
            'importAsExtern' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaImportAsExtern'),
            'spfRuns' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaSpfRuns'),
            'borderRouterCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaBdrRtrCount'),
            'asBorderRouterCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaAsBdrRtrCount'),
            'scopeLsaCount' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaScopeLsaCount'),
            'scopeLsaChecksumSum' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaScopeLsaCksumSum'),
            'summary' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaSummary'),
            'stubMetric' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaStubMetric'),
            'stubMetricCategory' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaStubMetricType'),
            'nssaTranslatorRole' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaNssaTranslatorRole'),
            'nssaTranslatorState' => MatchFilter::make()->setType('text')->setColumn('ospfv3AreaNssaTranslatorState'),
            'nssaTranslatorStabilityInterval' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaNssaTranslatorStabInterval'),
            'nssaTranslatorEvents' => MatchFilter::make()->setType('integer')->setColumn('ospfv3AreaNssaTranslatorEvents'),
            'isTrafficEngineeringEnabled' => MatchFilter::make()->setType('bool')->setColumn('ospfv3AreaTEEnabled'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'areaId' => SortableFilter::make()->setColumn('ospfv3AreaId'),
            'importAsExtern' => SortableFilter::make()->setColumn('ospfv3AreaImportAsExtern'),
            'spfRuns' => SortableFilter::make()->setColumn('ospfv3AreaSpfRuns'),
            'borderRouterCount' => SortableFilter::make()->setColumn('ospfv3AreaBdrRtrCount'),
            'asBorderRouterCount' => SortableFilter::make()->setColumn('ospfv3AreaAsBdrRtrCount'),
            'scopeLsaCount' => SortableFilter::make()->setColumn('ospfv3AreaScopeLsaCount'),
            'scopeLsaChecksumSum' => SortableFilter::make()->setColumn('ospfv3AreaScopeLsaCksumSum'),
            'summary' => SortableFilter::make()->setColumn('ospfv3AreaSummary'),
            'stubMetric' => SortableFilter::make()->setColumn('ospfv3AreaStubMetric'),
            'stubMetricCategory' => SortableFilter::make()->setColumn('ospfv3AreaStubMetricType'),
            'nssaTranslatorRole' => SortableFilter::make()->setColumn('ospfv3AreaNssaTranslatorRole'),
            'nssaTranslatorState' => SortableFilter::make()->setColumn('ospfv3AreaNssaTranslatorState'),
            'nssaTranslatorStabilityInterval' => SortableFilter::make()->setColumn('ospfv3AreaNssaTranslatorStabInterval'),
            'nssaTranslatorEvents' => SortableFilter::make()->setColumn('ospfv3AreaNssaTranslatorEvents'),
            'isTrafficEngineeringEnabled' => SortableFilter::make()->setColumn('ospfv3AreaTEEnabled'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('areaId', fn ($value, $model) => $model->ospfv3AreaId)->readonly(),
            field('importAsExtern', fn ($value, $model) => $model->ospfv3AreaImportAsExtern)->readonly(),
            field('spfRuns', fn ($value, $model) => $model->ospfv3AreaSpfRuns)->readonly(),
            field('borderRouterCount', fn ($value, $model) => $model->ospfv3AreaBdrRtrCount)->readonly(),
            field('asBorderRouterCount', fn ($value, $model) => $model->ospfv3AreaAsBdrRtrCount)->readonly(),
            field('scopeLsaCount', fn ($value, $model) => $model->ospfv3AreaScopeLsaCount)->readonly(),
            field('scopeLsaChecksumSum', fn ($value, $model) => $model->ospfv3AreaScopeLsaCksumSum)->readonly(),
            field('summary', fn ($value, $model) => $model->ospfv3AreaSummary)->readonly(),
            field('stubMetric', fn ($value, $model) => $model->ospfv3AreaStubMetric)->readonly(),
            field('stubMetricCategory', fn ($value, $model) => $model->ospfv3AreaStubMetricType)->readonly(),
            field('nssaTranslatorRole', fn ($value, $model) => $model->ospfv3AreaNssaTranslatorRole)->readonly(),
            field('nssaTranslatorState', fn ($value, $model) => $model->ospfv3AreaNssaTranslatorState)->readonly(),
            field('nssaTranslatorStabilityInterval', fn ($value, $model) => $model->ospfv3AreaNssaTranslatorStabInterval)->readonly(),
            field('nssaTranslatorEvents', fn ($value, $model) => $model->ospfv3AreaNssaTranslatorEvents)->readonly(),
            field('isTrafficEngineeringEnabled', fn ($value, $model) => $model->ospfv3AreaTEEnabled)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * OSPFv3 areas are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 areas are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
