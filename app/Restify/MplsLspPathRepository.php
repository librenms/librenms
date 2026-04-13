<?php

namespace App\Restify;

use App\Models\MplsLspPath;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MplsLspPathRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsLspPath::class;

    public static string $id = 'lsp_path_id';

    public static string $title = 'mplsLspPathType';

    public static array $search = [
        'mplsLspPathType',
        'mplsLspPathFailNodeAddr',
    ];

    public static array $match = [
        'lsp_id' => 'integer',
        'path_oid' => 'integer',
        'device_id' => 'integer',
        'mplsLspPathRowStatus' => 'text',
        'mplsLspPathLastChange' => 'integer',
        'mplsLspPathType' => 'text',
        'mplsLspPathBandwidth' => 'integer',
        'mplsLspPathOperBandwidth' => 'integer',
        'mplsLspPathAdminState' => 'text',
        'mplsLspPathOperState' => 'text',
        'mplsLspPathState' => 'text',
        'mplsLspPathFailCode' => 'text',
        'mplsLspPathFailNodeAddr' => 'text',
        'mplsLspPathMetric' => 'integer',
        'mplsLspPathOperMetric' => 'integer',
        'mplsLspPathTimeUp' => 'integer',
        'mplsLspPathTimeDown' => 'integer',
        'mplsLspPathTransitionCount' => 'integer',
        'mplsLspPathTunnelARHopListIndex' => 'integer',
        'mplsLspPathTunnelCHopListIndex' => 'integer',
    ];

    public static array $sort = [
        'lsp_id',
        'path_oid',
        'device_id',
        'mplsLspPathRowStatus',
        'mplsLspPathLastChange',
        'mplsLspPathType',
        'mplsLspPathBandwidth',
        'mplsLspPathOperBandwidth',
        'mplsLspPathAdminState',
        'mplsLspPathOperState',
        'mplsLspPathState',
        'mplsLspPathFailCode',
        'mplsLspPathFailNodeAddr',
        'mplsLspPathMetric',
        'mplsLspPathOperMetric',
        'mplsLspPathTimeUp',
        'mplsLspPathTimeDown',
        'mplsLspPathTransitionCount',
        'mplsLspPathTunnelARHopListIndex',
        'mplsLspPathTunnelCHopListIndex',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'lsp' => BelongsTo::make('lsp', MplsLspRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('lsp_id')->readonly(),
            field('path_oid')->readonly(),
            field('device_id')->readonly(),
            field('mplsLspPathRowStatus')->readonly(),
            field('mplsLspPathLastChange')->readonly(),
            field('mplsLspPathType')->readonly(),
            field('mplsLspPathBandwidth')->readonly(),
            field('mplsLspPathOperBandwidth')->readonly(),
            field('mplsLspPathAdminState')->readonly(),
            field('mplsLspPathOperState')->readonly(),
            field('mplsLspPathState')->readonly(),
            field('mplsLspPathFailCode')->readonly(),
            field('mplsLspPathFailNodeAddr')->readonly(),
            field('mplsLspPathMetric')->readonly(),
            field('mplsLspPathOperMetric')->readonly(),
            field('mplsLspPathTimeUp')->readonly(),
            field('mplsLspPathTimeDown')->readonly(),
            field('mplsLspPathTransitionCount')->readonly(),
            field('mplsLspPathTunnelARHopListIndex')->readonly(),
            field('mplsLspPathTunnelCHopListIndex')->readonly(),
        ];
    }

    /**
     * MPLS LSP paths are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS LSP paths are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
