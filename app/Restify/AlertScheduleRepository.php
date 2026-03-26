<?php

namespace App\Restify;

use App\Models\AlertSchedule;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AlertScheduleRepository extends Repository
{
    public static string $model = AlertSchedule::class;

    public static string $title = 'title';

    public static array $search = [
        'title',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('title')->rules('required', 'string'),
            field('notes')->rules('nullable', 'string'),
            field('recurring')->rules('boolean'),
            field('start')->readonly(),
            field('end')->readonly(),
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
