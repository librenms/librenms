<?php

namespace App\Restify;

use App\Models\Sensor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class SensorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Sensor::class;

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
            'description' => MatchFilter::make()->setType('text')->setColumn('sensor_descr'),
            'category' => MatchFilter::make()->setType('text')->setColumn('sensor_type'),
            'oid' => MatchFilter::make()->setType('text')->setColumn('sensor_oid'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('sensor_index'),
            'current' => MatchFilter::make()->setType('integer')->setColumn('sensor_current'),
            'previous' => MatchFilter::make()->setType('integer')->setColumn('sensor_prev'),
            'limit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit'),
            'warningLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_warn'),
            'lowLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_low'),
            'lowWarningLimit' => MatchFilter::make()->setType('integer')->setColumn('sensor_limit_low_warn'),
            'divisor' => MatchFilter::make()->setType('integer')->setColumn('sensor_divisor'),
            'multiplier' => MatchFilter::make()->setType('integer')->setColumn('sensor_multiplier'),
            'isAlerting' => MatchFilter::make()->setType('bool')->setColumn('sensor_alert'),
            'custom' => MatchFilter::make()->setType('text')->setColumn('sensor_custom'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('lastupdate'),
            'group' => MatchFilter::make()->setType('text')->setColumn('group'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'class' => SortableFilter::make()->setColumn('sensor_class'),
            'description' => SortableFilter::make()->setColumn('sensor_descr'),
            'category' => SortableFilter::make()->setColumn('sensor_type'),
            'oid' => SortableFilter::make()->setColumn('sensor_oid'),
            'index' => SortableFilter::make()->setColumn('sensor_index'),
            'current' => SortableFilter::make()->setColumn('sensor_current'),
            'previous' => SortableFilter::make()->setColumn('sensor_prev'),
            'limit' => SortableFilter::make()->setColumn('sensor_limit'),
            'warningLimit' => SortableFilter::make()->setColumn('sensor_limit_warn'),
            'lowLimit' => SortableFilter::make()->setColumn('sensor_limit_low'),
            'lowWarningLimit' => SortableFilter::make()->setColumn('sensor_limit_low_warn'),
            'divisor' => SortableFilter::make()->setColumn('sensor_divisor'),
            'multiplier' => SortableFilter::make()->setColumn('sensor_multiplier'),
            'isAlerting' => SortableFilter::make()->setColumn('sensor_alert'),
            'custom' => SortableFilter::make()->setColumn('sensor_custom'),
            'updatedAt' => SortableFilter::make()->setColumn('lastupdate'),
            'group' => SortableFilter::make()->setColumn('group'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('class', fn ($value, $model) => $model->sensor_class)->readonly(),
            field('description', fn ($value, $model) => $model->sensor_descr)->readonly(),
            field('category', fn ($value, $model) => $model->sensor_type)->readonly(),
            field('oid', fn ($value, $model) => $model->sensor_oid)->readonly(),
            field('index', fn ($value, $model) => $model->sensor_index)->readonly(),
            field('current', fn ($value, $model) => $model->sensor_current)->readonly(),
            field('previous', fn ($value, $model) => $model->sensor_prev)->readonly(),
            field('limit', fn ($value, $model) => $model->sensor_limit)->readonly(),
            field('warningLimit', fn ($value, $model) => $model->sensor_limit_warn)->readonly(),
            field('lowLimit', fn ($value, $model) => $model->sensor_limit_low)->readonly(),
            field('lowWarningLimit', fn ($value, $model) => $model->sensor_limit_low_warn)->readonly(),
            field('divisor', fn ($value, $model) => $model->sensor_divisor)->readonly(),
            field('multiplier', fn ($value, $model) => $model->sensor_multiplier)->readonly(),
            field('isAlerting', fn ($value, $model) => $model->sensor_alert)->readonly(),
            field('custom', fn ($value, $model) => $model->sensor_custom)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->lastupdate)->readonly(),
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
