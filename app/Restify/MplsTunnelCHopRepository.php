<?php

namespace App\Restify;

use App\Models\MplsTunnelCHop;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MplsTunnelCHopRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsTunnelCHop::class;

    public static string $id = 'c_hop_id';

    public static string $title = 'mplsTunnelCHopIpv4Addr';

    public static array $search = [
        'mplsTunnelCHopIpv4Addr',
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
            field('mplsTunnelCHopListIndex')->readonly(),
            field('mplsTunnelCHopIndex')->readonly(),
            field('lsp_path_id')->readonly(),
            field('device_id')->readonly(),
            field('mplsTunnelCHopAddrType')->readonly(),
            field('mplsTunnelCHopIpv4Addr')->readonly(),
            field('mplsTunnelCHopIpv6Addr')->readonly(),
            field('mplsTunnelCHopAsNumber')->readonly(),
            field('mplsTunnelCHopStrictOrLoose')->readonly(),
            field('mplsTunnelCHopRouterId')->readonly(),
        ];
    }

    /**
     * MPLS tunnel C hops are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS tunnel C hops are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
