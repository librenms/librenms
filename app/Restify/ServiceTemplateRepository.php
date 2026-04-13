<?php

namespace App\Restify;

use App\Models\ServiceTemplate;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class ServiceTemplateRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = ServiceTemplate::class;

    public static string $title = 'name';

    public static array $search = [
        'name',
        'desc',
    ];

    public static array $match = [
        'name' => 'text',
        'check' => 'text',
        'type' => 'text',
        'desc' => 'text',
        'ip' => 'text',
        'disabled' => 'integer',
    ];

    public static array $sort = [
        'name',
        'check',
        'type',
        'desc',
        'ip',
        'disabled',
    ];

    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string'),
            field('check')->rules('required', 'string'),
            field('type')->rules('required', 'string', 'in:static,dynamic'),
            field('desc')->rules('nullable', 'string'),
            field('param')->rules('nullable', 'string'),
            field('ip')->rules('nullable', 'string'),
            field('disabled')->rules('boolean'),
            field('rules')->readonly(),
        ];
    }
}
