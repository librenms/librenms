<?php

namespace App\Restify;

use App\Models\Ospfv3Area;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ospfv3AreaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Ospfv3Area::class;

    public static string $title = 'ospfv3AreaId';

    public static array $search = [
        'ospfv3AreaId',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'ospfv3_instance_id' => 'integer',
        'context_name' => 'text',
        'ospfv3AreaId' => 'integer',
        'ospfv3AreaImportAsExtern' => 'text',
        'ospfv3AreaSpfRuns' => 'integer',
        'ospfv3AreaBdrRtrCount' => 'integer',
        'ospfv3AreaAsBdrRtrCount' => 'integer',
        'ospfv3AreaScopeLsaCount' => 'integer',
        'ospfv3AreaScopeLsaCksumSum' => 'integer',
        'ospfv3AreaSummary' => 'text',
        'ospfv3AreaStubMetric' => 'integer',
        'ospfv3AreaStubMetricType' => 'text',
        'ospfv3AreaNssaTranslatorRole' => 'text',
        'ospfv3AreaNssaTranslatorState' => 'text',
        'ospfv3AreaNssaTranslatorStabInterval' => 'integer',
        'ospfv3AreaNssaTranslatorEvents' => 'integer',
        'ospfv3AreaTEEnabled' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'ospfv3_instance_id',
        'context_name',
        'ospfv3AreaId',
        'ospfv3AreaImportAsExtern',
        'ospfv3AreaSpfRuns',
        'ospfv3AreaBdrRtrCount',
        'ospfv3AreaAsBdrRtrCount',
        'ospfv3AreaScopeLsaCount',
        'ospfv3AreaScopeLsaCksumSum',
        'ospfv3AreaSummary',
        'ospfv3AreaStubMetric',
        'ospfv3AreaStubMetricType',
        'ospfv3AreaNssaTranslatorRole',
        'ospfv3AreaNssaTranslatorState',
        'ospfv3AreaNssaTranslatorStabInterval',
        'ospfv3AreaNssaTranslatorEvents',
        'ospfv3AreaTEEnabled',
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
            field('ospfv3_instance_id')->readonly(),
            field('context_name')->readonly(),
            field('ospfv3AreaId')->readonly(),
            field('ospfv3AreaImportAsExtern')->readonly(),
            field('ospfv3AreaSpfRuns')->readonly(),
            field('ospfv3AreaBdrRtrCount')->readonly(),
            field('ospfv3AreaAsBdrRtrCount')->readonly(),
            field('ospfv3AreaScopeLsaCount')->readonly(),
            field('ospfv3AreaScopeLsaCksumSum')->readonly(),
            field('ospfv3AreaSummary')->readonly(),
            field('ospfv3AreaStubMetric')->readonly(),
            field('ospfv3AreaStubMetricType')->readonly(),
            field('ospfv3AreaNssaTranslatorRole')->readonly(),
            field('ospfv3AreaNssaTranslatorState')->readonly(),
            field('ospfv3AreaNssaTranslatorStabInterval')->readonly(),
            field('ospfv3AreaNssaTranslatorEvents')->readonly(),
            field('ospfv3AreaTEEnabled')->readonly(),
        ];
    }

    /**
     * OSPFv3 areas are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 areas are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
