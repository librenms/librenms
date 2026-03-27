<?php

namespace App\Restify;

use App\Models\EntPhysical;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class InventoryRepository extends Repository
{
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

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
