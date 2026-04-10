<?php

namespace App\Restify;

use App\Models\MplsLsp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class MplsLspRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsLsp::class;

    public static string $id = 'lsp_id';

    public static string $title = 'mplsLspName';

    public static array $search = [
        'mplsLspName',
        'mplsLspFromAddr',
        'mplsLspToAddr',
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
            field('vrf_oid')->readonly(),
            field('lsp_oid')->readonly(),
            field('mplsLspRowStatus')->readonly(),
            field('mplsLspLastChange')->readonly(),
            field('mplsLspName')->readonly(),
            field('mplsLspAdminState')->readonly(),
            field('mplsLspOperState')->readonly(),
            field('mplsLspFromAddr')->readonly(),
            field('mplsLspToAddr')->readonly(),
            field('mplsLspType')->readonly(),
            field('mplsLspFastReroute')->readonly(),
            field('mplsLspAge')->readonly(),
            field('mplsLspTimeUp')->readonly(),
            field('mplsLspTimeDown')->readonly(),
            field('mplsLspPrimaryTimeUp')->readonly(),
            field('mplsLspTransitions')->readonly(),
            field('mplsLspLastTransition')->readonly(),
            field('mplsLspConfiguredPaths')->readonly(),
            field('mplsLspStandbyPaths')->readonly(),
            field('mplsLspOperationalPaths')->readonly(),
        ];
    }

    /**
     * MPLS LSPs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS LSPs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
