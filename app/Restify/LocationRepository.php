<?php

namespace App\Restify;

use App\Models\Location;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class LocationRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Location::class;

    public static string $title = 'location';




    public static function related(): array
    {
        return [
            'devices' => HasMany::make('devices', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('location'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('location'),
            'latitude' => MatchFilter::make()->setType('integer')->setColumn('lat'),
            'longitude' => MatchFilter::make()->setType('integer')->setColumn('lng'),
            'hasFixedCoordinates' => MatchFilter::make()->setType('bool')->setColumn('fixed_coordinates'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('location'),
            'latitude' => SortableFilter::make()->setColumn('lat'),
            'longitude' => SortableFilter::make()->setColumn('lng'),
            'hasFixedCoordinates' => SortableFilter::make()->setColumn('fixed_coordinates'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name', fn ($value, $model) => $model->location)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->location = $request->input($attribute);
                    }
                })
                ->rules('required', 'string'),
            field('latitude', fn ($value, $model) => $model->lat)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->lat = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'numeric'),
            field('longitude', fn ($value, $model) => $model->lng)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->lng = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'numeric'),
            field('hasFixedCoordinates', fn ($value, $model) => $model->fixed_coordinates)->readonly(),
        ];
    }
}
