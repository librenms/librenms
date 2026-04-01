<?php

namespace App\Restify;

use App\Models\MplsSap;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsSapRepository extends Repository
{
    public static string $model = MplsSap::class;

    public static string $id = 'sap_id';

    public static string $title = 'sapDescription';

    public static array $search = [
        'sapDescription',
        'ifName',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'service' => BelongsTo::make('service', MplsServiceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('svc_id')->readonly(),
            field('svc_oid')->readonly(),
            field('sapPortId')->readonly(),
            field('ifName')->readonly(),
            field('sapEncapValue')->readonly(),
            field('device_id')->readonly(),
            field('sapRowStatus')->readonly(),
            field('sapType')->readonly(),
            field('sapDescription')->readonly(),
            field('sapAdminStatus')->readonly(),
            field('sapOperStatus')->readonly(),
            field('sapLastMgmtChange')->readonly(),
            field('sapLastStatusChange')->readonly(),
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
     * MPLS SAPs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SAPs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
