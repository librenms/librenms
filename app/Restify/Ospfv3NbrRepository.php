<?php

namespace App\Restify;

use App\Models\Ospfv3Nbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class Ospfv3NbrRepository extends Repository
{
    public static string $model = Ospfv3Nbr::class;

    public static string $title = 'ospfv3NbrRtrId';

    public static array $search = [
        'ospfv3NbrRtrId',
        'ospfv3NbrAddress',
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
            field('port_id')->readonly(),
            field('context_name')->readonly(),
            field('ospfv3NbrRtrId')->readonly(),
            field('ospfv3NbrAddress')->readonly(),
            field('ospfv3NbrAddressType')->readonly(),
            field('ospfv3NbrOptions')->readonly(),
            field('ospfv3NbrPriority')->readonly(),
            field('ospfv3NbrState')->readonly(),
            field('ospfv3NbrEvents')->readonly(),
            field('ospfv3NbrLsRetransQLen')->readonly(),
            field('ospfv3NbmaNbrStatus')->readonly(),
            field('ospfv3NbmaNbrPermanence')->readonly(),
            field('ospfv3NbrHelloSuppressed')->readonly(),
            field('ospfv3NbrIfId')->readonly(),
            field('ospfv3NbrRestartHelperStatus')->readonly(),
            field('ospfv3NbrRestartHelperAge')->readonly(),
            field('ospfv3NbrRestartHelperExitReason')->readonly(),
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
     * OSPFv3 neighbors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPFv3 neighbors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
