<?php

namespace App\Restify;

use App\Models\Service;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class ServiceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Service::class;

    public static string $id = 'service_id';

    public static string $title = 'service_name';

    public static array $search = [
        'service_type',
        'service_desc',
        'service_name',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->rules('required', 'integer'),
            field('service_type')->rules('required', 'string'),
            field('service_name')->rules('nullable', 'string'),
            field('service_desc')->rules('nullable', 'string'),
            field('service_param')->rules('nullable', 'string'),
            field('service_ip')->rules('nullable', 'string'),
            field('service_status')->readonly(),
            field('service_message')->readonly(),
            field('service_changed')->readonly(),
            field('service_ignore')->rules('boolean'),
            field('service_disabled')->rules('boolean'),
            field('service_template_id')->rules('nullable', 'integer'),
        ];
    }
}
