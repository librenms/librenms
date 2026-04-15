<?php

namespace App\Restify;

use App\Models\BgpPeer;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class BgpPeerRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = BgpPeer::class;

    public static string $id = 'bgpPeer_id';

    public static string $title = 'bgpPeerIdentifier';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'identifier' => SearchableFilter::make()->setColumn('bgpPeerIdentifier'),
            'remoteAddress' => SearchableFilter::make()->setColumn('bgpPeerRemoteAddr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'identifier' => MatchFilter::make()->setType('text')->setColumn('bgpPeerIdentifier'),
            'remoteAs' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerRemoteAs'),
            'state' => MatchFilter::make()->setType('text')->setColumn('bgpPeerState'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('bgpPeerAdminStatus'),
            'localAddress' => MatchFilter::make()->setType('text')->setColumn('bgpLocalAddr'),
            'remoteAddress' => MatchFilter::make()->setType('text')->setColumn('bgpPeerRemoteAddr'),
            'description' => MatchFilter::make()->setType('text')->setColumn('bgpPeerDescr'),
            'interface' => MatchFilter::make()->setType('text')->setColumn('bgpPeerIface'),
            'asText' => MatchFilter::make()->setType('text')->setColumn('astext'),
            'inUpdates' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerInUpdates'),
            'outUpdates' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerOutUpdates'),
            'inTotalMessages' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerInTotalMessages'),
            'outTotalMessages' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerOutTotalMessages'),
            'fsmEstablishedTime' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerFsmEstablishedTime'),
            'inUpdateElapsedTime' => MatchFilter::make()->setType('integer')->setColumn('bgpPeerInUpdateElapsedTime'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'identifier' => SortableFilter::make()->setColumn('bgpPeerIdentifier'),
            'remoteAs' => SortableFilter::make()->setColumn('bgpPeerRemoteAs'),
            'state' => SortableFilter::make()->setColumn('bgpPeerState'),
            'adminStatus' => SortableFilter::make()->setColumn('bgpPeerAdminStatus'),
            'localAddress' => SortableFilter::make()->setColumn('bgpLocalAddr'),
            'remoteAddress' => SortableFilter::make()->setColumn('bgpPeerRemoteAddr'),
            'description' => SortableFilter::make()->setColumn('bgpPeerDescr'),
            'interface' => SortableFilter::make()->setColumn('bgpPeerIface'),
            'asText' => SortableFilter::make()->setColumn('astext'),
            'inUpdates' => SortableFilter::make()->setColumn('bgpPeerInUpdates'),
            'outUpdates' => SortableFilter::make()->setColumn('bgpPeerOutUpdates'),
            'inTotalMessages' => SortableFilter::make()->setColumn('bgpPeerInTotalMessages'),
            'outTotalMessages' => SortableFilter::make()->setColumn('bgpPeerOutTotalMessages'),
            'fsmEstablishedTime' => SortableFilter::make()->setColumn('bgpPeerFsmEstablishedTime'),
            'inUpdateElapsedTime' => SortableFilter::make()->setColumn('bgpPeerInUpdateElapsedTime'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('identifier', fn ($value, $model) => $model->bgpPeerIdentifier)->readonly(),
            field('remoteAs', fn ($value, $model) => $model->bgpPeerRemoteAs)->readonly(),
            field('state', fn ($value, $model) => $model->bgpPeerState)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->bgpPeerAdminStatus)->readonly(),
            field('localAddress', fn ($value, $model) => $model->bgpLocalAddr)->readonly(),
            field('remoteAddress', fn ($value, $model) => $model->bgpPeerRemoteAddr)->readonly(),
            field('description', fn ($value, $model) => $model->bgpPeerDescr)->readonly(),
            field('interface', fn ($value, $model) => $model->bgpPeerIface)->readonly(),
            field('asText', fn ($value, $model) => $model->astext)->readonly(),
            field('inUpdates', fn ($value, $model) => $model->bgpPeerInUpdates)->readonly(),
            field('outUpdates', fn ($value, $model) => $model->bgpPeerOutUpdates)->readonly(),
            field('inTotalMessages', fn ($value, $model) => $model->bgpPeerInTotalMessages)->readonly(),
            field('outTotalMessages', fn ($value, $model) => $model->bgpPeerOutTotalMessages)->readonly(),
            field('fsmEstablishedTime', fn ($value, $model) => $model->bgpPeerFsmEstablishedTime)->readonly(),
            field('inUpdateElapsedTime', fn ($value, $model) => $model->bgpPeerInUpdateElapsedTime)->readonly(),
        ];
    }

    /**
     * BGP peers are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * BGP peers are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
