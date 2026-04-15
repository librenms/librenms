<?php

namespace App\Restify;

use App\Models\MplsLspPath;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsLspPathRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsLspPath::class;

    public static string $id = 'lsp_path_id';

    public static string $title = 'mplsLspPathType';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'lsp' => BelongsTo::make('lsp', MplsLspRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'oid' => MatchFilter::make()->setType('text')->setColumn('path_oid'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathRowStatus'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('mplsLspPathLastChange'),
            'category' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathType'),
            'bandwidth' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathBandwidth'),
            'operationalBandwidth' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathOperBandwidth'),
            'adminState' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathAdminState'),
            'operationalState' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathOperState'),
            'state' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathState'),
            'failCode' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathFailCode'),
            'failNodeAddress' => MatchFilter::make()->setType('text')->setColumn('mplsLspPathFailNodeAddr'),
            'metric' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathMetric'),
            'operationalMetric' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathOperMetric'),
            'timeUp' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathTimeUp'),
            'timeDown' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathTimeDown'),
            'transitionCount' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathTransitionCount'),
            'tunnelArHopListIndex' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathTunnelARHopListIndex'),
            'tunnelCHopListIndex' => MatchFilter::make()->setType('integer')->setColumn('mplsLspPathTunnelCHopListIndex'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'oid' => SortableFilter::make()->setColumn('path_oid'),
            'rowStatus' => SortableFilter::make()->setColumn('mplsLspPathRowStatus'),
            'updatedAt' => SortableFilter::make()->setColumn('mplsLspPathLastChange'),
            'category' => SortableFilter::make()->setColumn('mplsLspPathType'),
            'bandwidth' => SortableFilter::make()->setColumn('mplsLspPathBandwidth'),
            'operationalBandwidth' => SortableFilter::make()->setColumn('mplsLspPathOperBandwidth'),
            'adminState' => SortableFilter::make()->setColumn('mplsLspPathAdminState'),
            'operationalState' => SortableFilter::make()->setColumn('mplsLspPathOperState'),
            'state' => SortableFilter::make()->setColumn('mplsLspPathState'),
            'failCode' => SortableFilter::make()->setColumn('mplsLspPathFailCode'),
            'failNodeAddress' => SortableFilter::make()->setColumn('mplsLspPathFailNodeAddr'),
            'metric' => SortableFilter::make()->setColumn('mplsLspPathMetric'),
            'operationalMetric' => SortableFilter::make()->setColumn('mplsLspPathOperMetric'),
            'timeUp' => SortableFilter::make()->setColumn('mplsLspPathTimeUp'),
            'timeDown' => SortableFilter::make()->setColumn('mplsLspPathTimeDown'),
            'transitionCount' => SortableFilter::make()->setColumn('mplsLspPathTransitionCount'),
            'tunnelArHopListIndex' => SortableFilter::make()->setColumn('mplsLspPathTunnelARHopListIndex'),
            'tunnelCHopListIndex' => SortableFilter::make()->setColumn('mplsLspPathTunnelCHopListIndex'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('oid', fn ($value, $model) => $model->path_oid)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->mplsLspPathRowStatus)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->mplsLspPathLastChange)->readonly(),
            field('category', fn ($value, $model) => $model->mplsLspPathType)->readonly(),
            field('bandwidth', fn ($value, $model) => $model->mplsLspPathBandwidth)->readonly(),
            field('operationalBandwidth', fn ($value, $model) => $model->mplsLspPathOperBandwidth)->readonly(),
            field('adminState', fn ($value, $model) => $model->mplsLspPathAdminState)->readonly(),
            field('operationalState', fn ($value, $model) => $model->mplsLspPathOperState)->readonly(),
            field('state', fn ($value, $model) => $model->mplsLspPathState)->readonly(),
            field('failCode', fn ($value, $model) => $model->mplsLspPathFailCode)->readonly(),
            field('failNodeAddress', fn ($value, $model) => $model->mplsLspPathFailNodeAddr)->readonly(),
            field('metric', fn ($value, $model) => $model->mplsLspPathMetric)->readonly(),
            field('operationalMetric', fn ($value, $model) => $model->mplsLspPathOperMetric)->readonly(),
            field('timeUp', fn ($value, $model) => $model->mplsLspPathTimeUp)->readonly(),
            field('timeDown', fn ($value, $model) => $model->mplsLspPathTimeDown)->readonly(),
            field('transitionCount', fn ($value, $model) => $model->mplsLspPathTransitionCount)->readonly(),
            field('tunnelArHopListIndex', fn ($value, $model) => $model->mplsLspPathTunnelARHopListIndex)->readonly(),
            field('tunnelCHopListIndex', fn ($value, $model) => $model->mplsLspPathTunnelCHopListIndex)->readonly(),
        ];
    }

    /**
     * MPLS LSP paths are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS LSP paths are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
