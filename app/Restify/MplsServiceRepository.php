<?php

namespace App\Restify;

use App\Models\MplsService;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsServiceRepository extends Repository
{
    public static string $model = MplsService::class;

    /**
     * Override to avoid ambiguity with the existing ServiceRepository ('services').
     */
    public static string $uriKey = 'mpls-services';

    public static string $id = 'svc_id';

    public static string $title = 'svcDescription';

    public static array $search = [
        'svcDescription',
        'svcType',
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
            field('svc_oid')->readonly(),
            field('device_id')->readonly(),
            field('svcRowStatus')->readonly(),
            field('svcType')->readonly(),
            field('svcCustId')->readonly(),
            field('svcAdminStatus')->readonly(),
            field('svcOperStatus')->readonly(),
            field('svcDescription')->readonly(),
            field('svcMtu')->readonly(),
            field('svcNumSaps')->readonly(),
            field('svcNumSdps')->readonly(),
            field('svcLastMgmtChange')->readonly(),
            field('svcLastStatusChange')->readonly(),
            field('svcVRouterId')->readonly(),
            field('svcTlsMacLearning')->readonly(),
            field('svcTlsStpAdminStatus')->readonly(),
            field('svcTlsStpOperStatus')->readonly(),
            field('svcTlsFdbTableSize')->readonly(),
            field('svcTlsFdbNumEntries')->readonly(),
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
     * MPLS services are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS services are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
