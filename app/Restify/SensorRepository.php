<?php

namespace App\Restify;

use App\Models\Sensor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class SensorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Sensor::class;

    public static string $id = 'sensor_id';

    public static string $title = 'sensor_descr';

    public static array $search = [
        'sensor_descr',
        'sensor_class',
        'sensor_type',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'sensor_class' => 'text',
        'sensor_descr' => 'text',
        'sensor_type' => 'text',
        'sensor_oid' => 'text',
        'sensor_index' => 'text',
        'sensor_current' => 'integer',
        'sensor_prev' => 'integer',
        'sensor_limit' => 'integer',
        'sensor_limit_warn' => 'integer',
        'sensor_limit_low' => 'integer',
        'sensor_limit_low_warn' => 'integer',
        'sensor_divisor' => 'integer',
        'sensor_multiplier' => 'integer',
        'sensor_alert' => 'integer',
        'sensor_custom' => 'text',
        'lastupdate' => 'datetime',
        'group' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'sensor_class',
        'sensor_descr',
        'sensor_type',
        'sensor_oid',
        'sensor_index',
        'sensor_current',
        'sensor_prev',
        'sensor_limit',
        'sensor_limit_warn',
        'sensor_limit_low',
        'sensor_limit_low_warn',
        'sensor_divisor',
        'sensor_multiplier',
        'sensor_alert',
        'sensor_custom',
        'lastupdate',
        'group',
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
            field('device_id')->readonly(),
            field('sensor_class')->readonly(),
            field('sensor_descr')->readonly(),
            field('sensor_type')->readonly(),
            field('sensor_oid')->readonly(),
            field('sensor_index')->readonly(),
            field('sensor_current')->readonly(),
            field('sensor_prev')->readonly(),
            field('sensor_limit')->readonly(),
            field('sensor_limit_warn')->readonly(),
            field('sensor_limit_low')->readonly(),
            field('sensor_limit_low_warn')->readonly(),
            field('sensor_divisor')->readonly(),
            field('sensor_multiplier')->readonly(),
            field('sensor_alert')->readonly(),
            field('sensor_custom')->readonly(),
            field('lastupdate')->readonly(),
            field('group')->readonly(),
        ];
    }

    /**
     * Sensors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Sensors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
