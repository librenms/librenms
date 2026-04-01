<?php

namespace App\Restify;

use App\Models\MplsSdp;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsSdpRepository extends Repository
{
    public static string $model = MplsSdp::class;

    public static string $id = 'sdp_id';

    public static string $title = 'sdpDescription';

    public static array $search = [
        'sdpDescription',
        'sdpFarEndInetAddress',
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
            field('sdp_oid')->readonly(),
            field('device_id')->readonly(),
            field('sdpRowStatus')->readonly(),
            field('sdpDelivery')->readonly(),
            field('sdpDescription')->readonly(),
            field('sdpAdminStatus')->readonly(),
            field('sdpOperStatus')->readonly(),
            field('sdpAdminPathMtu')->readonly(),
            field('sdpOperPathMtu')->readonly(),
            field('sdpLastMgmtChange')->readonly(),
            field('sdpLastStatusChange')->readonly(),
            field('sdpActiveLspType')->readonly(),
            field('sdpFarEndInetAddressType')->readonly(),
            field('sdpFarEndInetAddress')->readonly(),
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
     * MPLS SDPs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SDPs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
