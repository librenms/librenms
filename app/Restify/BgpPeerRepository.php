<?php

namespace App\Restify;

use App\Models\BgpPeer;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class BgpPeerRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = BgpPeer::class;

    public static string $id = 'bgpPeer_id';

    public static string $title = 'bgpPeerIdentifier';

    public static array $search = [
        'bgpPeerIdentifier',
        'bgpPeerRemoteAddr',
        'bgpPeerDescr',
        'astext',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'vrf_id' => 'integer',
        'bgpPeerIdentifier' => 'text',
        'bgpPeerRemoteAs' => 'integer',
        'bgpPeerState' => 'text',
        'bgpPeerAdminStatus' => 'text',
        'bgpLocalAddr' => 'text',
        'bgpPeerRemoteAddr' => 'text',
        'bgpPeerDescr' => 'text',
        'bgpPeerIface' => 'integer',
        'astext' => 'text',
        'bgpPeerInUpdates' => 'integer',
        'bgpPeerOutUpdates' => 'integer',
        'bgpPeerInTotalMessages' => 'integer',
        'bgpPeerOutTotalMessages' => 'integer',
        'bgpPeerFsmEstablishedTime' => 'integer',
        'bgpPeerInUpdateElapsedTime' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'vrf_id',
        'bgpPeerIdentifier',
        'bgpPeerRemoteAs',
        'bgpPeerState',
        'bgpPeerAdminStatus',
        'bgpLocalAddr',
        'bgpPeerRemoteAddr',
        'bgpPeerDescr',
        'bgpPeerIface',
        'astext',
        'bgpPeerInUpdates',
        'bgpPeerOutUpdates',
        'bgpPeerInTotalMessages',
        'bgpPeerOutTotalMessages',
        'bgpPeerFsmEstablishedTime',
        'bgpPeerInUpdateElapsedTime',
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
            field('vrf_id')->readonly(),
            field('bgpPeerIdentifier')->readonly(),
            field('bgpPeerRemoteAs')->readonly(),
            field('bgpPeerState')->readonly(),
            field('bgpPeerAdminStatus')->readonly(),
            field('bgpLocalAddr')->readonly(),
            field('bgpPeerRemoteAddr')->readonly(),
            field('bgpPeerDescr')->readonly(),
            field('bgpPeerIface')->readonly(),
            field('astext')->readonly(),
            field('bgpPeerInUpdates')->readonly(),
            field('bgpPeerOutUpdates')->readonly(),
            field('bgpPeerInTotalMessages')->readonly(),
            field('bgpPeerOutTotalMessages')->readonly(),
            field('bgpPeerFsmEstablishedTime')->readonly(),
            field('bgpPeerInUpdateElapsedTime')->readonly(),
        ];
    }

    /**
     * BGP peers are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * BGP peers are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
