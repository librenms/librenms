<?php

namespace App\Restify;

use App\Models\OspfArea;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class OspfAreaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfArea::class;

    public static string $title = 'ospfAreaId';

    public static array $search = [
        'ospfAreaId',
        'context_name',
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
            field('ospfAreaId')->readonly(),
            field('ospfAuthType')->readonly(),
            field('ospfImportAsExtern')->readonly(),
            field('ospfSpfRuns')->readonly(),
            field('ospfAreaBdrRtrCount')->readonly(),
            field('ospfAsBdrRtrCount')->readonly(),
            field('ospfAreaLsaCount')->readonly(),
            field('ospfAreaLsaCksumSum')->readonly(),
            field('ospfAreaSummary')->readonly(),
            field('ospfAreaStatus')->readonly(),
            field('context_name')->readonly(),
        ];
    }

    /**
     * OSPF areas are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF areas are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
