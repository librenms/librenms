<?php

namespace App\Restify;

use App\Models\Ospfv3Area;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ospfv3AreaRepository extends Repository
{
    public static string $model = Ospfv3Area::class;

    public static string $title = 'ospfv3AreaId';

    public static array $search = [
        'ospfv3AreaId',
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
