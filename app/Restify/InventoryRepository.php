<?php

namespace App\Restify;

use App\Models\EntPhysical;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class InventoryRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = EntPhysical::class;

    public static string $uriKey = 'inventory';

    public static string $id = 'entPhysical_id';

    public static string $title = 'entPhysicalName';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('entPhysicalName'),
            'serialNumber' => SearchableFilter::make()->setColumn('entPhysicalSerialNum'),
        ];
    }

    public static function matches(): array
    {
        return [
            'index' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalIndex'),
            'description' => MatchFilter::make()->setType('text')->setColumn('entPhysicalDescr'),
            'class' => MatchFilter::make()->setType('text')->setColumn('entPhysicalClass'),
            'name' => MatchFilter::make()->setType('text')->setColumn('entPhysicalName'),
            'serialNumber' => MatchFilter::make()->setType('text')->setColumn('entPhysicalSerialNum'),
            'modelName' => MatchFilter::make()->setType('text')->setColumn('entPhysicalModelName'),
            'manufacturerName' => MatchFilter::make()->setType('text')->setColumn('entPhysicalMfgName'),
            'containedIn' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalContainedIn'),
            'parentRelativePosition' => MatchFilter::make()->setType('integer')->setColumn('entPhysicalParentRelPos'),
            'hardwareRevision' => MatchFilter::make()->setType('text')->setColumn('entPhysicalHardwareRev'),
            'firmwareRevision' => MatchFilter::make()->setType('text')->setColumn('entPhysicalFirmwareRev'),
            'softwareRevision' => MatchFilter::make()->setType('text')->setColumn('entPhysicalSoftwareRev'),
            'isFieldReplaceable' => MatchFilter::make()->setType('bool')->setColumn('entPhysicalIsFRU'),
            'alias' => MatchFilter::make()->setType('text')->setColumn('entPhysicalAlias'),
            'interfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('ifIndex'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'index' => SortableFilter::make()->setColumn('entPhysicalIndex'),
            'description' => SortableFilter::make()->setColumn('entPhysicalDescr'),
            'class' => SortableFilter::make()->setColumn('entPhysicalClass'),
            'name' => SortableFilter::make()->setColumn('entPhysicalName'),
            'serialNumber' => SortableFilter::make()->setColumn('entPhysicalSerialNum'),
            'modelName' => SortableFilter::make()->setColumn('entPhysicalModelName'),
            'manufacturerName' => SortableFilter::make()->setColumn('entPhysicalMfgName'),
            'containedIn' => SortableFilter::make()->setColumn('entPhysicalContainedIn'),
            'parentRelativePosition' => SortableFilter::make()->setColumn('entPhysicalParentRelPos'),
            'hardwareRevision' => SortableFilter::make()->setColumn('entPhysicalHardwareRev'),
            'firmwareRevision' => SortableFilter::make()->setColumn('entPhysicalFirmwareRev'),
            'softwareRevision' => SortableFilter::make()->setColumn('entPhysicalSoftwareRev'),
            'isFieldReplaceable' => SortableFilter::make()->setColumn('entPhysicalIsFRU'),
            'alias' => SortableFilter::make()->setColumn('entPhysicalAlias'),
            'interfaceIndex' => SortableFilter::make()->setColumn('ifIndex'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('index', fn ($value, $model) => $model->entPhysicalIndex)->readonly(),
            field('description', fn ($value, $model) => $model->entPhysicalDescr)->readonly(),
            field('class', fn ($value, $model) => $model->entPhysicalClass)->readonly(),
            field('name', fn ($value, $model) => $model->entPhysicalName)->readonly(),
            field('serialNumber', fn ($value, $model) => $model->entPhysicalSerialNum)->readonly(),
            field('modelName', fn ($value, $model) => $model->entPhysicalModelName)->readonly(),
            field('manufacturerName', fn ($value, $model) => $model->entPhysicalMfgName)->readonly(),
            field('containedIn', fn ($value, $model) => $model->entPhysicalContainedIn)->readonly(),
            field('parentRelativePosition', fn ($value, $model) => $model->entPhysicalParentRelPos)->readonly(),
            field('hardwareRevision', fn ($value, $model) => $model->entPhysicalHardwareRev)->readonly(),
            field('firmwareRevision', fn ($value, $model) => $model->entPhysicalFirmwareRev)->readonly(),
            field('softwareRevision', fn ($value, $model) => $model->entPhysicalSoftwareRev)->readonly(),
            field('isFieldReplaceable', fn ($value, $model) => $model->entPhysicalIsFRU)->readonly(),
            field('alias', fn ($value, $model) => $model->entPhysicalAlias)->readonly(),
            field('interfaceIndex', fn ($value, $model) => $model->ifIndex)->readonly(),
        ];
    }

    /**
     * Inventory entries are discovered automatically via ENTITY-MIB during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Inventory entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
