<?php

namespace App\Restify;

use App\Models\Ospfv3Nbr;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class Ospfv3NbrRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Ospfv3Nbr::class;

    public static string $title = 'ospfv3NbrRtrId';

    public static array $search = [
        'ospfv3NbrRtrId',
        'ospfv3NbrAddress',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'ospfv3_instance_id' => 'integer',
        'port_id' => 'integer',
        'context_name' => 'text',
        'ospfv3NbrRtrId' => 'integer',
        'ospfv3NbrAddress' => 'text',
        'ospfv3NbrAddressType' => 'text',
        'ospfv3NbrOptions' => 'integer',
        'ospfv3NbrPriority' => 'integer',
        'ospfv3NbrState' => 'text',
        'ospfv3NbrEvents' => 'integer',
        'ospfv3NbrLsRetransQLen' => 'integer',
        'ospfv3NbmaNbrStatus' => 'text',
        'ospfv3NbmaNbrPermanence' => 'text',
        'ospfv3NbrHelloSuppressed' => 'text',
        'ospfv3NbrIfId' => 'integer',
        'ospfv3NbrRestartHelperStatus' => 'text',
        'ospfv3NbrRestartHelperAge' => 'integer',
        'ospfv3NbrRestartHelperExitReason' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'ospfv3_instance_id',
        'port_id',
        'context_name',
        'ospfv3NbrRtrId',
        'ospfv3NbrAddress',
        'ospfv3NbrAddressType',
        'ospfv3NbrOptions',
        'ospfv3NbrPriority',
        'ospfv3NbrState',
        'ospfv3NbrEvents',
        'ospfv3NbrLsRetransQLen',
        'ospfv3NbmaNbrStatus',
        'ospfv3NbmaNbrPermanence',
        'ospfv3NbrHelloSuppressed',
        'ospfv3NbrIfId',
        'ospfv3NbrRestartHelperStatus',
        'ospfv3NbrRestartHelperAge',
        'ospfv3NbrRestartHelperExitReason',
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
