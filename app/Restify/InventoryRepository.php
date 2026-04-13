<?php

namespace App\Restify;

use App\Models\EntPhysical;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class InventoryRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = EntPhysical::class;

    public static string $uriKey = 'inventory';

    public static string $id = 'entPhysical_id';

    public static string $title = 'entPhysicalName';

    public static array $search = [
        'entPhysicalDescr',
        'entPhysicalName',
        'entPhysicalSerialNum',
        'entPhysicalModelName',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'entPhysicalIndex' => 'integer',
        'entPhysicalDescr' => 'text',
        'entPhysicalClass' => 'text',
        'entPhysicalName' => 'text',
        'entPhysicalSerialNum' => 'text',
        'entPhysicalModelName' => 'text',
        'entPhysicalMfgName' => 'text',
        'entPhysicalContainedIn' => 'integer',
        'entPhysicalParentRelPos' => 'integer',
        'entPhysicalHardwareRev' => 'text',
        'entPhysicalFirmwareRev' => 'text',
        'entPhysicalSoftwareRev' => 'text',
        'entPhysicalIsFRU' => 'text',
        'entPhysicalAlias' => 'text',
        'ifIndex' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'entPhysicalIndex',
        'entPhysicalDescr',
        'entPhysicalClass',
        'entPhysicalName',
        'entPhysicalSerialNum',
        'entPhysicalModelName',
        'entPhysicalMfgName',
        'entPhysicalContainedIn',
        'entPhysicalParentRelPos',
        'entPhysicalHardwareRev',
        'entPhysicalFirmwareRev',
        'entPhysicalSoftwareRev',
        'entPhysicalIsFRU',
        'entPhysicalAlias',
        'ifIndex',
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
            field('entPhysicalIndex')->readonly(),
            field('entPhysicalDescr')->readonly(),
            field('entPhysicalClass')->readonly(),
            field('entPhysicalName')->readonly(),
            field('entPhysicalSerialNum')->readonly(),
            field('entPhysicalModelName')->readonly(),
            field('entPhysicalMfgName')->readonly(),
            field('entPhysicalContainedIn')->readonly(),
            field('entPhysicalParentRelPos')->readonly(),
            field('entPhysicalHardwareRev')->readonly(),
            field('entPhysicalFirmwareRev')->readonly(),
            field('entPhysicalSoftwareRev')->readonly(),
            field('entPhysicalIsFRU')->readonly(),
            field('entPhysicalAlias')->readonly(),
            field('ifIndex')->readonly(),
        ];
    }

    /**
     * Inventory entries are discovered automatically via ENTITY-MIB during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Inventory entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
