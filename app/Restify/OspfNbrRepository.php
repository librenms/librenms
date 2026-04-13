<?php

namespace App\Restify;

use App\Models\OspfNbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class OspfNbrRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = OspfNbr::class;

    public static string $title = 'ospfNbrIpAddr';

    public static array $search = [
        'ospfNbrIpAddr',
        'ospfNbrRtrId',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'port_id' => 'integer',
        'ospf_nbr_id' => 'text',
        'ospfNbrIpAddr' => 'text',
        'ospfNbrAddressLessIndex' => 'integer',
        'ospfNbrRtrId' => 'text',
        'ospfNbrOptions' => 'integer',
        'ospfNbrPriority' => 'integer',
        'ospfNbrState' => 'text',
        'ospfNbrEvents' => 'integer',
        'ospfNbrLsRetransQLen' => 'integer',
        'ospfNbmaNbrStatus' => 'text',
        'ospfNbmaNbrPermanence' => 'text',
        'ospfNbrHelloSuppressed' => 'text',
        'context_name' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'port_id',
        'ospf_nbr_id',
        'ospfNbrIpAddr',
        'ospfNbrAddressLessIndex',
        'ospfNbrRtrId',
        'ospfNbrOptions',
        'ospfNbrPriority',
        'ospfNbrState',
        'ospfNbrEvents',
        'ospfNbrLsRetransQLen',
        'ospfNbmaNbrStatus',
        'ospfNbmaNbrPermanence',
        'ospfNbrHelloSuppressed',
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
