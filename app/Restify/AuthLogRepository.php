<?php

namespace App\Restify;

use App\Models\AuthLog;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AuthLogRepository extends Repository
{
    public static string $model = AuthLog::class;

    public static string $title = 'user';




    public static function searchables(): array
    {
        return [
            'user' => SearchableFilter::make()->setColumn('user'),
        ];
    }

    public static function matches(): array
    {
        return [
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('datetime'),
            'user' => MatchFilter::make()->setType('text')->setColumn('user'),
            'address' => MatchFilter::make()->setType('text')->setColumn('address'),
            'result' => MatchFilter::make()->setType('text')->setColumn('result'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'createdAt' => SortableFilter::make()->setColumn('datetime'),
            'user' => SortableFilter::make()->setColumn('user'),
            'address' => SortableFilter::make()->setColumn('address'),
            'result' => SortableFilter::make()->setColumn('result'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('createdAt', fn ($value, $model) => $model->datetime)->readonly(),
            field('user')->readonly(),
            field('address')->readonly(),
            field('result')->readonly(),
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
     * Auth logs are generated internally by the authentication system — not created manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Auth logs are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
