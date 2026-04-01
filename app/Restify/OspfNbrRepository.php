<?php

namespace App\Restify;

use App\Models\OspfNbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class OspfNbrRepository extends Repository
{
    public static string $model = OspfNbr::class;

    public static string $title = 'ospfNbrIpAddr';

    public static array $search = [
        'ospfNbrIpAddr',
        'ospfNbrRtrId',
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
            field('port_id')->readonly(),
            field('ospf_nbr_id')->readonly(),
            field('ospfNbrIpAddr')->readonly(),
            field('ospfNbrAddressLessIndex')->readonly(),
            field('ospfNbrRtrId')->readonly(),
            field('ospfNbrOptions')->readonly(),
            field('ospfNbrPriority')->readonly(),
            field('ospfNbrState')->readonly(),
            field('ospfNbrEvents')->readonly(),
            field('ospfNbrLsRetransQLen')->readonly(),
            field('ospfNbmaNbrStatus')->readonly(),
            field('ospfNbmaNbrPermanence')->readonly(),
            field('ospfNbrHelloSuppressed')->readonly(),
            field('context_name')->readonly(),
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
     * OSPF neighbors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * OSPF neighbors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
