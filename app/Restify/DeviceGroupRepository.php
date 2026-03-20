<?php

namespace App\Restify;

use App\Models\DeviceGroup;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class DeviceGroupRepository extends Repository
{
    public static string $model = DeviceGroup::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
        'desc',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('desc')->rules('nullable', 'string', 'max:255'),
            field('type')->rules('required', 'string', 'in:dynamic,static'),
            field('rules')->readonly(),
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
