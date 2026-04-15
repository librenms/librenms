<?php

namespace App\Restify;

use App\Models\AlertSchedule;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertScheduleRepository extends Repository
{
    public static string $model = AlertSchedule::class;

    public static string $title = 'title';




    public static function searchables(): array
    {
        return [
            'title' => SearchableFilter::make()->setColumn('title'),
        ];
    }

    public static function matches(): array
    {
        return [
            'title' => MatchFilter::make()->setType('text')->setColumn('title'),
            'notes' => MatchFilter::make()->setType('text')->setColumn('notes'),
            'isRecurring' => MatchFilter::make()->setType('bool')->setColumn('recurring'),
            'scheduleStartAt' => MatchFilter::make()->setType('datetime')->setColumn('start'),
            'scheduleEndAt' => MatchFilter::make()->setType('datetime')->setColumn('end'),
            'status' => MatchFilter::make()->setType('text')->setColumn('status'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'title' => SortableFilter::make()->setColumn('title'),
            'notes' => SortableFilter::make()->setColumn('notes'),
            'isRecurring' => SortableFilter::make()->setColumn('recurring'),
            'scheduleStartAt' => SortableFilter::make()->setColumn('start'),
            'scheduleEndAt' => SortableFilter::make()->setColumn('end'),
            'status' => SortableFilter::make()->setColumn('status'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('title')->rules('required', 'string'),
            field('notes')->rules('nullable', 'string'),
            field('isRecurring', fn ($value, $model) => $model->recurring)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->recurring = $request->input($attribute);
                    }
                })
                ->rules('boolean'),
            field('scheduleStartAt', fn ($value, $model) => $model->start)->readonly(),
            field('scheduleEndAt', fn ($value, $model) => $model->end)->readonly(),
            field('status')->readonly(),
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
