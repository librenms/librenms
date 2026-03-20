<?php

namespace App\Restify;

use App\Models\AlertTemplate;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AlertTemplateRepository extends Repository
{
    public static string $model = AlertTemplate::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('template')->rules('required', 'string'),
            field('title')->rules('nullable', 'string', 'max:255'),
            field('title_rec')->rules('nullable', 'string', 'max:255'),
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
