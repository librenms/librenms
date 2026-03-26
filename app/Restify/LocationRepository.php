<?php

namespace App\Restify;

use App\Models\Location;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LocationRepository extends Repository
{
    public static string $model = Location::class;

    public static string $title = 'location';

    public static array $search = [
        'location',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('location')->rules('required', 'string'),
            field('lat')->rules('nullable', 'numeric'),
            field('lng')->rules('nullable', 'numeric'),
            field('fixed_coordinates')->readonly(),
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
