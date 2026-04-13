<?php

namespace App\Restify;

use App\Models\WirelessSensor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class WirelessSensorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = WirelessSensor::class;

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
        'sensor_index' => 'text',
        'sensor_type' => 'text',
        'sensor_descr' => 'text',
        'sensor_divisor' => 'integer',
        'sensor_multiplier' => 'integer',
        'sensor_aggregator' => 'text',
        'sensor_current' => 'integer',
        'sensor_prev' => 'integer',
        'sensor_limit' => 'integer',
        'sensor_limit_warn' => 'integer',
        'sensor_limit_low' => 'integer',
        'sensor_limit_low_warn' => 'integer',
        'sensor_alert' => 'integer',
        'sensor_custom' => 'text',
        'entPhysicalIndex' => 'text',
        'entPhysicalIndex_measured' => 'text',
        'lastupdate' => 'datetime',
        'access_point_id' => 'integer',
        'rrd_type' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'sensor_class',
        'sensor_index',
        'sensor_type',
        'sensor_descr',
        'sensor_divisor',
        'sensor_multiplier',
        'sensor_aggregator',
        'sensor_current',
        'sensor_prev',
        'sensor_limit',
        'sensor_limit_warn',
        'sensor_limit_low',
        'sensor_limit_low_warn',
        'sensor_alert',
        'sensor_custom',
        'entPhysicalIndex',
        'entPhysicalIndex_measured',
        'lastupdate',
        'access_point_id',
        'rrd_type',
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
            field('sensor_index')->readonly(),
            field('sensor_type')->readonly(),
            field('sensor_descr')->readonly(),
            field('sensor_divisor')->readonly(),
            field('sensor_multiplier')->readonly(),
            field('sensor_aggregator')->readonly(),
            field('sensor_current')->readonly(),
            field('sensor_prev')->readonly(),
            field('sensor_limit')->readonly(),
            field('sensor_limit_warn')->readonly(),
            field('sensor_limit_low')->readonly(),
            field('sensor_limit_low_warn')->readonly(),
            field('sensor_alert')->readonly(),
            field('sensor_custom')->readonly(),
            field('entPhysicalIndex')->readonly(),
            field('entPhysicalIndex_measured')->readonly(),
            field('lastupdate')->readonly(),
            field('access_point_id')->readonly(),
            field('rrd_type')->readonly(),
        ];
    }

    /**
     * Wireless sensors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Wireless sensors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
