<?php

namespace App\Restify;

use App\Models\AccessPoint;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class AccessPointRepository extends Repository
{
    public static string $model = AccessPoint::class;

    public static string $id = 'accesspoint_id';

    public static string $title = 'name';

    public static array $search = [
        'name',
        'mac_addr',
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
            field('name')->readonly(),
            field('radio_number')->readonly(),
            field('type')->readonly(),
            field('mac_addr')->readonly(),
            field('channel')->readonly(),
            field('txpow')->readonly(),
            field('radioutil')->readonly(),
            field('numasoclients')->readonly(),
            field('nummonclients')->readonly(),
            field('numactbssid')->readonly(),
            field('nummonbssid')->readonly(),
            field('interference')->readonly(),
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
     * Access points are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Access points are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
