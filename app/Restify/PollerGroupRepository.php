<?php

namespace App\Restify;

use App\Models\PollerGroup;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PollerGroupRepository extends Repository
{
    public static string $model = PollerGroup::class;

    public static string $title = 'group_name';




    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('group_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('group_name'),
            'description' => MatchFilter::make()->setType('text')->setColumn('descr'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('group_name'),
            'description' => SortableFilter::make()->setColumn('descr'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name', fn ($value, $model) => $model->group_name)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->group_name = $request->input($attribute);
                    }
                })
                ->rules('required', 'string', 'max:255'),
            field('description', fn ($value, $model) => $model->descr)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->descr = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
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
}
