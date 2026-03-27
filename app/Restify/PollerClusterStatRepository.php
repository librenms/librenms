<?php

namespace App\Restify;

use App\Models\PollerClusterStat;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class PollerClusterStatRepository extends Repository
{
    public static string $model = PollerClusterStat::class;

    public static string $title = 'poller_type';

    public function fields(RestifyRequest $request): array
    {
        return [
            field('parent_poller')->readonly(),
            field('poller_type')->readonly(),
            field('depth')->readonly(),
            field('devices')->readonly(),
            field('worker_seconds')->readonly(),
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

    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
