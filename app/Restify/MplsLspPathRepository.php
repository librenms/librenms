<?php

namespace App\Restify;

use App\Models\MplsLspPath;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsLspPathRepository extends Repository
{
    public static string $model = MplsLspPath::class;

    public static string $id = 'lsp_path_id';

    public static string $title = 'mplsLspPathType';

    public static array $search = [
        'mplsLspPathType',
        'mplsLspPathFailNodeAddr',
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
