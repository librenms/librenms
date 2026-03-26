<?php

namespace App\Restify;

use App\Models\PollerGroup;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class PollerGroupRepository extends Repository
{
    public static string $model = PollerGroup::class;

    public static string $title = 'group_name';

    public static array $search = [
        'group_name',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('group_name')->rules('required', 'string', 'max:255'),
            field('descr')->rules('nullable', 'string'),
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
