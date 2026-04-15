<?php

namespace App\Restify;

use App\Models\AlertTemplate;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertTemplateRepository extends Repository
{
    public static string $model = AlertTemplate::class;

    public static string $title = 'name';




    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'template' => MatchFilter::make()->setType('text')->setColumn('template'),
            'title' => MatchFilter::make()->setType('text')->setColumn('title'),
            'recoveryTitle' => MatchFilter::make()->setType('text')->setColumn('title_rec'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'template' => SortableFilter::make()->setColumn('template'),
            'title' => SortableFilter::make()->setColumn('title'),
            'recoveryTitle' => SortableFilter::make()->setColumn('title_rec'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('template')->rules('required', 'string'),
            field('title')->rules('nullable', 'string', 'max:255'),
            field('recoveryTitle', fn ($value, $model) => $model->title_rec)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->title_rec = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string', 'max:255'),
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
