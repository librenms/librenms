<?php

namespace App\Restify;

use App\Models\MplsSdpBind;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MplsSdpBindRepository extends Repository
{
    public static string $model = MplsSdpBind::class;

    public static string $id = 'bind_id';

    public static string $title = 'bind_id';

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'sdp' => BelongsTo::make('sdp', MplsSdpRepository::class),
            'service' => BelongsTo::make('service', MplsServiceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('sdp_id')->readonly(),
            field('svc_id')->readonly(),
            field('sdp_oid')->readonly(),
            field('svc_oid')->readonly(),
            field('device_id')->readonly(),
            field('sdpBindRowStatus')->readonly(),
            field('sdpBindAdminStatus')->readonly(),
            field('sdpBindOperStatus')->readonly(),
            field('sdpBindLastMgmtChange')->readonly(),
            field('sdpBindLastStatusChange')->readonly(),
            field('sdpBindType')->readonly(),
            field('sdpBindVcType')->readonly(),
            field('sdpBindBaseStatsIngFwdPackets')->readonly(),
            field('sdpBindBaseStatsIngFwdOctets')->readonly(),
            field('sdpBindBaseStatsEgrFwdPackets')->readonly(),
            field('sdpBindBaseStatsEgrFwdOctets')->readonly(),
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
     * MPLS SDP bindings are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS SDP bindings are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
