<?php

namespace App\Restify;

use App\Models\MplsTunnelArHop;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MplsTunnelArHopRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsTunnelArHop::class;

    public static string $id = 'ar_hop_id';

    public static string $title = 'mplsTunnelARHopIpv4Addr';

    public static array $search = [
        'mplsTunnelARHopIpv4Addr',
    ];

    public static array $match = [
        'mplsTunnelARHopListIndex' => 'integer',
        'mplsTunnelARHopIndex' => 'integer',
        'lsp_path_id' => 'integer',
        'device_id' => 'integer',
        'mplsTunnelARHopAddrType' => 'text',
        'mplsTunnelARHopIpv4Addr' => 'text',
        'mplsTunnelARHopIpv6Addr' => 'text',
        'mplsTunnelARHopAsNumber' => 'integer',
        'mplsTunnelARHopStrictOrLoose' => 'text',
        'mplsTunnelARHopRouterId' => 'text',
        'localProtected' => 'text',
        'linkProtectionInUse' => 'text',
        'bandwidthProtected' => 'text',
        'nextNodeProtected' => 'text',
    ];

    public static array $sort = [
        'mplsTunnelARHopListIndex',
        'mplsTunnelARHopIndex',
        'lsp_path_id',
        'device_id',
        'mplsTunnelARHopAddrType',
        'mplsTunnelARHopIpv4Addr',
        'mplsTunnelARHopIpv6Addr',
        'mplsTunnelARHopAsNumber',
        'mplsTunnelARHopStrictOrLoose',
        'mplsTunnelARHopRouterId',
        'localProtected',
        'linkProtectionInUse',
        'bandwidthProtected',
        'nextNodeProtected',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'lspPath' => BelongsTo::make('lspPath', MplsLspPathRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('mplsTunnelARHopListIndex')->readonly(),
            field('mplsTunnelARHopIndex')->readonly(),
            field('lsp_path_id')->readonly(),
            field('device_id')->readonly(),
            field('mplsTunnelARHopAddrType')->readonly(),
            field('mplsTunnelARHopIpv4Addr')->readonly(),
            field('mplsTunnelARHopIpv6Addr')->readonly(),
            field('mplsTunnelARHopAsNumber')->readonly(),
            field('mplsTunnelARHopStrictOrLoose')->readonly(),
            field('mplsTunnelARHopRouterId')->readonly(),
            field('localProtected')->readonly(),
            field('linkProtectionInUse')->readonly(),
            field('bandwidthProtected')->readonly(),
            field('nextNodeProtected')->readonly(),
        ];
    }

    /**
     * MPLS tunnel AR hops are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS tunnel AR hops are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
