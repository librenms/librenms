<?php

namespace App\Restify;

use App\Models\AlertRule;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class AlertRuleRepository extends Repository
{
    public static string $model = AlertRule::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('severity')->rules('required', 'in:ok,warning,critical'),
            field('disabled')->rules('required', 'boolean'),
            field('rule')->readonly(),
            field('query')->readonly(),
            field('builder')->readonly(),
            field('extra')->readonly(),
            field('proc')->rules('nullable', 'string', 'max:80'),
            field('notes')->rules('nullable', 'string'),
            field('invert_map')->rules('boolean'),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
