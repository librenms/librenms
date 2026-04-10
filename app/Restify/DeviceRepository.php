<?php

namespace App\Restify;

use App\Models\Device;
use App\Models\Location;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class DeviceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Device::class;

    public static string $id = 'device_id';

    public static string $title = 'hostname';

    public static array $search = [
        'hostname',
        'sysName',
        'os',
    ];

    public static function related(): array
    {
        return [
            'ports' => HasMany::make('ports', PortRepository::class),
            'sensors' => HasMany::make('sensors', SensorRepository::class),
            'processors' => HasMany::make('processors', ProcessorRepository::class),
            'mempools' => HasMany::make('mempools', MempoolRepository::class),
            'storage' => HasMany::make('storage', StorageRepository::class),
            'services' => HasMany::make('services', ServiceRepository::class),
            'bgpPeers' => HasMany::make('bgpPeers', BgpPeerRepository::class),
            'components' => HasMany::make('components', ComponentRepository::class),
            'applications' => HasMany::make('applications', ApplicationRepository::class),
            'inventory' => HasMany::make('entityPhysical', InventoryRepository::class),
            'eventlogs' => HasMany::make('eventlogs', EventlogRepository::class),
            'syslogs' => HasMany::make('syslogs', SyslogRepository::class),
            'alertLogs' => HasMany::make('alertLogs', AlertLogRepository::class),
            'location' => BelongsTo::make('location', LocationRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            // Read-only identifying / discovered fields
            field('hostname')->readonly(),
            field('sysName')->readonly(),
            field('sysDescr')->readonly(),
            field('sysObjectID')->readonly(),
            field('os')->readonly(),
            field('status')->readonly(),
            field('status_reason')->readonly(),
            field('hardware')->readonly(),
            field('serial')->readonly(),
            field('version')->readonly(),
            field('features')->readonly(),
            field('ip')->readonly(),
            field('uptime')->readonly(),
            field('notes')->readonly(),

            // Writable: simple passthrough (column name == API name)
            field('display')->rules('nullable', 'string', 'max:128'),
            field('overwrite_ip')->rules('nullable', 'string', 'ip', 'max:128'),
            field('purpose')->rules('nullable', 'string', 'max:200'),
            field('type')->rules('nullable', 'string', 'max:128'),

            // Writable: positive boolean (inverse of `disabled` column)
            field('is_polling_enabled', fn ($value, $device) => ! $device->disabled)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->disabled = ! $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),

            // Writable: positive boolean (inverse of `disable_notify` column)
            field('is_alerting_enabled', fn ($value, $device) => ! $device->disable_notify)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->disable_notify = ! $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),

            // Writable: rename of `ignore` column
            field('is_ignored', fn ($value, $device) => (bool) $device->ignore)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->ignore = $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),

            // Writable: rename of `ignore_status` column
            field('is_status_ignored', fn ($value, $device) => (bool) $device->ignore_status)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->ignore_status = $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),

            // Writable: rename of `override_sysLocation` column
            field('sys_location_override', fn ($value, $device) => (bool) $device->override_sysLocation)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->override_sysLocation = $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),

            // Writable: human-readable location string, resolved to/from `location_id`
            field('sys_location', fn ($value, $device) => $device->location?->location)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if (! $request->exists($attribute)) {
                        return;
                    }
                    $value = $request->input($attribute);
                    if (empty($value)) {
                        $model->location_id = null;

                        return;
                    }
                    $model->location_id = Location::firstOrCreate(['location' => $value])->id;
                })
                ->rules('nullable', 'string', 'max:255'),

            // Writable: rename of `poller_group` column
            field('poller_group_id', fn ($value, $device) => $device->poller_group)
                ->fillCallback(function (RestifyRequest $request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->poller_group = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'integer', 'exists:poller_groups,id'),
        ];
    }
}
