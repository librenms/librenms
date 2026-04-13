<?php

namespace App\Restify;

use App\Models\AlertRule;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class AlertRuleRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AlertRule::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
    ];

    public static array $match = [
        'name' => 'text',
        'severity' => 'text',
        'disabled' => 'integer',
        'invert_map' => 'integer',
    ];

    public static array $sort = [
        'name',
        'severity',
        'disabled',
        'invert_map',
    ];

    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
            'locations' => BelongsToMany::make('locations', LocationRepository::class),
        ];
    }

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
}
