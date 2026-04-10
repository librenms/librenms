<?php

namespace App\Restify;

use App\Models\Location;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class LocationRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Location::class;

    public static string $title = 'location';

    public static array $search = [
        'location',
    ];

    public static function related(): array
    {
        return [
            'devices' => HasMany::make('devices', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('location')->rules('required', 'string'),
            field('lat')->rules('nullable', 'numeric'),
            field('lng')->rules('nullable', 'numeric'),
            field('fixed_coordinates')->readonly(),
        ];
    }
}
