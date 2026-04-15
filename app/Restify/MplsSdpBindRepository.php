<?php

namespace App\Restify;

use App\Models\MplsSdpBind;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsSdpBindRepository extends Repository
{
    use DeviceScopedRepository;

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

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'sdpOid' => MatchFilter::make()->setType('text')->setColumn('sdp_oid'),
            'serviceOid' => MatchFilter::make()->setType('text')->setColumn('svc_oid'),
            'rowStatus' => MatchFilter::make()->setType('text')->setColumn('sdpBindRowStatus'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('sdpBindAdminStatus'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('sdpBindOperStatus'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('sdpBindLastMgmtChange'),
            'statusChangedAt' => MatchFilter::make()->setType('datetime')->setColumn('sdpBindLastStatusChange'),
            'category' => MatchFilter::make()->setType('text')->setColumn('sdpBindType'),
            'virtualCircuitCategory' => MatchFilter::make()->setType('text')->setColumn('sdpBindVcType'),
            'ingressForwardPackets' => MatchFilter::make()->setType('integer')->setColumn('sdpBindBaseStatsIngFwdPackets'),
            'ingressForwardOctets' => MatchFilter::make()->setType('integer')->setColumn('sdpBindBaseStatsIngFwdOctets'),
            'egressForwardPackets' => MatchFilter::make()->setType('integer')->setColumn('sdpBindBaseStatsEgrFwdPackets'),
            'egressForwardOctets' => MatchFilter::make()->setType('integer')->setColumn('sdpBindBaseStatsEgrFwdOctets'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'sdpOid' => SortableFilter::make()->setColumn('sdp_oid'),
            'serviceOid' => SortableFilter::make()->setColumn('svc_oid'),
            'rowStatus' => SortableFilter::make()->setColumn('sdpBindRowStatus'),
            'adminStatus' => SortableFilter::make()->setColumn('sdpBindAdminStatus'),
            'operationalStatus' => SortableFilter::make()->setColumn('sdpBindOperStatus'),
            'updatedAt' => SortableFilter::make()->setColumn('sdpBindLastMgmtChange'),
            'statusChangedAt' => SortableFilter::make()->setColumn('sdpBindLastStatusChange'),
            'category' => SortableFilter::make()->setColumn('sdpBindType'),
            'virtualCircuitCategory' => SortableFilter::make()->setColumn('sdpBindVcType'),
            'ingressForwardPackets' => SortableFilter::make()->setColumn('sdpBindBaseStatsIngFwdPackets'),
            'ingressForwardOctets' => SortableFilter::make()->setColumn('sdpBindBaseStatsIngFwdOctets'),
            'egressForwardPackets' => SortableFilter::make()->setColumn('sdpBindBaseStatsEgrFwdPackets'),
            'egressForwardOctets' => SortableFilter::make()->setColumn('sdpBindBaseStatsEgrFwdOctets'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('sdpOid', fn ($value, $model) => $model->sdp_oid)->readonly(),
            field('serviceOid', fn ($value, $model) => $model->svc_oid)->readonly(),
            field('rowStatus', fn ($value, $model) => $model->sdpBindRowStatus)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->sdpBindAdminStatus)->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->sdpBindOperStatus)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->sdpBindLastMgmtChange)->readonly(),
            field('statusChangedAt', fn ($value, $model) => $model->sdpBindLastStatusChange)->readonly(),
            field('category', fn ($value, $model) => $model->sdpBindType)->readonly(),
            field('virtualCircuitCategory', fn ($value, $model) => $model->sdpBindVcType)->readonly(),
            field('ingressForwardPackets', fn ($value, $model) => $model->sdpBindBaseStatsIngFwdPackets)->readonly(),
            field('ingressForwardOctets', fn ($value, $model) => $model->sdpBindBaseStatsIngFwdOctets)->readonly(),
            field('egressForwardPackets', fn ($value, $model) => $model->sdpBindBaseStatsEgrFwdPackets)->readonly(),
            field('egressForwardOctets', fn ($value, $model) => $model->sdpBindBaseStatsEgrFwdOctets)->readonly(),
        ];
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
