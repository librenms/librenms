<?php

namespace App\Restify;

use App\Models\WirelessSensor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class WirelessSensorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = WirelessSensor::class;

    public static string $id = 'sensor_id';

    public static string $title = 'sensor_descr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('sensor_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'class' => MatchFilter::make()->setType('text')->setColumn('sensor_class'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('sensor_index'),
            'category' => MatchFilter::make()->setType('text')->setColumn('sensor_type'),
            'description' => MatchFilter::make()->setType('text')->setColumn('sensor_descr'),
            'divisor' => MatchFilter::make()->setType('integer')->setColumn('sensor_divisor'),
            'multiplier' => MatchFilter::make()->setType('integer')->setColumn('sensor_multiplier'),
            'aggregator' => MatchFilter::make()->setType('text')->setColumn('sensor_aggregator'),
            'current' => MatchFilter::make()->setType('integer')->setColumn('sensor_current'),
            'previous' => MatchFilter::make()->setType('integer')->setColumn('sensor_prev'),
            'limit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit'),
            'warningLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_warn'),
            'lowLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_low'),
            'lowWarningLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_low_warn'),
            'isAlerting' => MatchFilter::make()->setType('bool')->setColumn('sensor_alert'),
            'custom' => MatchFilter::make()->setType('text')->setColumn('sensor_custom'),
            'entityPhysicalIndex' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalIndex'),
            'entityPhysicalIndexMeasured' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalIndex_measured'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('lastupdate'),
            'rrdCategory' => MatchFilter::make()->setType('text')->setColumn('rrd_type'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'class' => SortableFilter::make()->setColumn('sensor_class'),
            'index' => SortableFilter::make()->setColumn('sensor_index'),
            'category' => SortableFilter::make()->setColumn('sensor_type'),
            'description' => SortableFilter::make()->setColumn('sensor_descr'),
            'divisor' => SortableFilter::make()->setColumn('sensor_divisor'),
            'multiplier' => SortableFilter::make()->setColumn('sensor_multiplier'),
            'aggregator' => SortableFilter::make()->setColumn('sensor_aggregator'),
            'current' => SortableFilter::make()->setColumn('sensor_current'),
            'previous' => SortableFilter::make()->setColumn('sensor_prev'),
            'limit' => SortableFilter::make()->setColumn('sensor_limit'),
            'warningLimit' => SortableFilter::make()->setColumn('sensor_limit_warn'),
            'lowLimit' => SortableFilter::make()->setColumn('sensor_limit_low'),
            'lowWarningLimit' => SortableFilter::make()->setColumn('sensor_limit_low_warn'),
            'isAlerting' => SortableFilter::make()->setColumn('sensor_alert'),
            'custom' => SortableFilter::make()->setColumn('sensor_custom'),
            'entityPhysicalIndex' => SortableFilter::make()->setColumn('entPhysicalIndex'),
            'entityPhysicalIndexMeasured' => SortableFilter::make()->setColumn('entPhysicalIndex_measured'),
            'updatedAt' => SortableFilter::make()->setColumn('lastupdate'),
            'rrdCategory' => SortableFilter::make()->setColumn('rrd_type'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('class', fn ($value, $model) => $model->sensor_class)->readonly(),
            field('index', fn ($value, $model) => $model->sensor_index)->readonly(),
            field('category', fn ($value, $model) => $model->sensor_type)->readonly(),
            field('description', fn ($value, $model) => $model->sensor_descr)->readonly(),
            field('divisor', fn ($value, $model) => $model->sensor_divisor)->readonly(),
            field('multiplier', fn ($value, $model) => $model->sensor_multiplier)->readonly(),
            field('aggregator', fn ($value, $model) => $model->sensor_aggregator)->readonly(),
            field('current', fn ($value, $model) => $model->sensor_current)->readonly(),
            field('previous', fn ($value, $model) => $model->sensor_prev)->readonly(),
            field('limit', fn ($value, $model) => $model->sensor_limit)->readonly(),
            field('warningLimit', fn ($value, $model) => $model->sensor_limit_warn)->readonly(),
            field('lowLimit', fn ($value, $model) => $model->sensor_limit_low)->readonly(),
            field('lowWarningLimit', fn ($value, $model) => $model->sensor_limit_low_warn)->readonly(),
            field('isAlerting', fn ($value, $model) => $model->sensor_alert)->readonly(),
            field('custom', fn ($value, $model) => $model->sensor_custom)->readonly(),
            field('entityPhysicalIndex', fn ($value, $model) => $model->entPhysicalIndex)->readonly(),
            field('entityPhysicalIndexMeasured', fn ($value, $model) => $model->entPhysicalIndex_measured)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->lastupdate)->readonly(),
            field('rrdCategory', fn ($value, $model) => $model->rrd_type)->readonly(),
        ];
    }

    /**
     * Wireless sensors are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Wireless sensors are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
