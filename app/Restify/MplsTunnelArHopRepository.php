<?php

namespace App\Restify;

use App\Models\MplsTunnelArHop;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsTunnelArHopRepository extends Repository
{
    public static string $model = MplsTunnelArHop::class;

    public static string $id = 'ar_hop_id';

    public static string $title = 'mplsTunnelARHopIpv4Addr';

    public static array $search = [
        'mplsTunnelARHopIpv4Addr',
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
