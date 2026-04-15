<?php

namespace App\Restify;

use App\Models\PollerClusterStat;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PollerClusterStatRepository extends Repository
{
    public static string $model = PollerClusterStat::class;

    public static string $title = 'poller_type';



    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'parentPoller' => MatchFilter::make()->setType('text')->setColumn('parent_poller'),
            'category' => MatchFilter::make()->setType('text')->setColumn('poller_type'),
            'depth' => MatchFilter::make()->setType('integer')->setColumn('depth'),
            'deviceCount' => MatchFilter::make()->setType('integer')->setColumn('devices'),
            'workerSeconds' => MatchFilter::make()->setType('integer')->setColumn('worker_seconds'),
            'workers' => MatchFilter::make()->setType('integer')->setColumn('workers'),
            'frequency' => MatchFilter::make()->setType('integer')->setColumn('frequency'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'parentPoller' => SortableFilter::make()->setColumn('parent_poller'),
            'category' => SortableFilter::make()->setColumn('poller_type'),
            'depth' => SortableFilter::make()->setColumn('depth'),
            'deviceCount' => SortableFilter::make()->setColumn('devices'),
            'workerSeconds' => SortableFilter::make()->setColumn('worker_seconds'),
            'workers' => SortableFilter::make()->setColumn('workers'),
            'frequency' => SortableFilter::make()->setColumn('frequency'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('parentPoller', fn ($value, $model) => $model->parent_poller)->readonly(),
            field('category', fn ($value, $model) => $model->poller_type)->readonly(),
            field('depth')->readonly(),
            field('deviceCount', fn ($value, $model) => $model->devices)->readonly(),
            field('workerSeconds', fn ($value, $model) => $model->worker_seconds)->readonly(),
            field('workers')->readonly(),
            field('frequency')->readonly(),
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
     * Poller stats are recorded automatically during polling runs — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Poller stats are historical records managed by the poller lifecycle.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
