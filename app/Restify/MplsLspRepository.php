<?php

namespace App\Restify;

use App\Models\MplsLsp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsLspRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsLsp::class;

    public static string $id = 'lsp_id';

    public static string $title = 'mplsLspName';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('mplsLspName'),
        ];
    }

    public static function matches(): array
    {
        return [
            'vrfOid' => MatchFilter::make()->setType('text')->setColumn('vrf_oid'),
            'oid' => MatchFilter::make()->setType('text')->setColumn('lsp_oid'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('mplsLspRowStatus'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('mplsLspLastChange'),
            'name' => MatchFilter::make()->setType('text')->setColumn('mplsLspName'),
            'adminState' => MatchFilter::make()->setType('text')->setColumn('mplsLspAdminState'),
            'operationalState' => MatchFilter::make()->setType('text')->setColumn('mplsLspOperState'),
            'fromAddress' => MatchFilter::make()->setType('text')->setColumn('mplsLspFromAddr'),
            'toAddress' => MatchFilter::make()->setType('text')->setColumn('mplsLspToAddr'),
            'category' => MatchFilter::make()->setType('text')->setColumn('mplsLspType'),
            'fastReroute' => MatchFilter::make()->setType('text')->setColumn('mplsLspFastReroute'),
            'age' => MatchFilter::make()->setType('integer')->setColumn('mplsLspAge'),
            'timeUp' => MatchFilter::make()->setType('integer')->setColumn('mplsLspTimeUp'),
            'timeDown' => MatchFilter::make()->setType('integer')->setColumn('mplsLspTimeDown'),
            'primaryTimeUp' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPrimaryTimeUp'),
            'transitions' => MatchFilter::make()->setType('integer')->setColumn('mplsLspTransitions'),
            'lastTransitionAt' => MatchFilter::make()->setType('datetime')->setColumn('mplsLspLastTransition'),
            'configuredPaths' => MatchFilter::make()->setType('integer')->setColumn('mplsLspConfiguredPaths'),
            'standbyPaths' => MatchFilter::make()->setType('integer')->setColumn('mplsLspStandbyPaths'),
            'operationalPaths' => MatchFilter::make()->setType('integer')->setColumn('mplsLspOperationalPaths'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'vrfOid' => SortableFilter::make()->setColumn('vrf_oid'),
            'oid' => SortableFilter::make()->setColumn('lsp_oid'),
            'rowStatus' => SortableFilter::make()->setColumn('mplsLspRowStatus'),
            'updatedAt' => SortableFilter::make()->setColumn('mplsLspLastChange'),
            'name' => SortableFilter::make()->setColumn('mplsLspName'),
            'adminState' => SortableFilter::make()->setColumn('mplsLspAdminState'),
            'operationalState' => SortableFilter::make()->setColumn('mplsLspOperState'),
            'fromAddress' => SortableFilter::make()->setColumn('mplsLspFromAddr'),
            'toAddress' => SortableFilter::make()->setColumn('mplsLspToAddr'),
            'category' => SortableFilter::make()->setColumn('mplsLspType'),
            'fastReroute' => SortableFilter::make()->setColumn('mplsLspFastReroute'),
            'age' => SortableFilter::make()->setColumn('mplsLspAge'),
            'timeUp' => SortableFilter::make()->setColumn('mplsLspTimeUp'),
            'timeDown' => SortableFilter::make()->setColumn('mplsLspTimeDown'),
            'primaryTimeUp' => SortableFilter::make()->setColumn('mplsLspPrimaryTimeUp'),
            'transitions' => SortableFilter::make()->setColumn('mplsLspTransitions'),
            'lastTransitionAt' => SortableFilter::make()->setColumn('mplsLspLastTransition'),
            'configuredPaths' => SortableFilter::make()->setColumn('mplsLspConfiguredPaths'),
            'standbyPaths' => SortableFilter::make()->setColumn('mplsLspStandbyPaths'),
            'operationalPaths' => SortableFilter::make()->setColumn('mplsLspOperationalPaths'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('vrfOid', fn ($value, $model) => $model->vrf_oid)->readonly(),
            field('oid', fn ($value, $model) => $model->lsp_oid)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->mplsLspRowStatus)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->mplsLspLastChange)->readonly(),
            field('name', fn ($value, $model) => $model->mplsLspName)->readonly(),
            field('adminState', fn ($value, $model) => $model->mplsLspAdminState)->readonly(),
            field('operationalState', fn ($value, $model) => $model->mplsLspOperState)->readonly(),
            field('fromAddress', fn ($value, $model) => $model->mplsLspFromAddr)->readonly(),
            field('toAddress', fn ($value, $model) => $model->mplsLspToAddr)->readonly(),
            field('category', fn ($value, $model) => $model->mplsLspType)->readonly(),
            field('fastReroute', fn ($value, $model) => $model->mplsLspFastReroute)->readonly(),
            field('age', fn ($value, $model) => $model->mplsLspAge)->readonly(),
            field('timeUp', fn ($value, $model) => $model->mplsLspTimeUp)->readonly(),
            field('timeDown', fn ($value, $model) => $model->mplsLspTimeDown)->readonly(),
            field('primaryTimeUp', fn ($value, $model) => $model->mplsLspPrimaryTimeUp)->readonly(),
            field('transitions', fn ($value, $model) => $model->mplsLspTransitions)->readonly(),
            field('lastTransitionAt', fn ($value, $model) => $model->mplsLspLastTransition)->readonly(),
            field('configuredPaths', fn ($value, $model) => $model->mplsLspConfiguredPaths)->readonly(),
            field('standbyPaths', fn ($value, $model) => $model->mplsLspStandbyPaths)->readonly(),
            field('operationalPaths', fn ($value, $model) => $model->mplsLspOperationalPaths)->readonly(),
        ];
    }

    /**
     * MPLS LSPs are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS LSPs are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
