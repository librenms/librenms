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
