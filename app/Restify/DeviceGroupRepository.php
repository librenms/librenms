<?php

namespace App\Restify;

use App\Models\DeviceGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class DeviceGroupRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DeviceGroup::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
        'desc',
    ];

    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('desc')->rules('nullable', 'string', 'max:255'),
            field('type')->rules('required', 'string', 'in:dynamic,static'),
            field('rules')->readonly(),
        ];
    }
}
