<?php

namespace App\Restify;

use App\Models\MplsTunnelArHop;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsTunnelArHopRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsTunnelArHop::class;

    public static string $id = 'ar_hop_id';

    public static string $title = 'mplsTunnelARHopIpv4Addr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'lspPath' => BelongsTo::make('lspPath', MplsLspPathRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'listIndex' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelARHopListIndex'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelARHopIndex'),
            'addressCategory' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelARHopAddrType'),
            'ipv4Address' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelARHopIpv4Addr'),
            'ipv6Address' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelARHopIpv6Addr'),
            'asNumber' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelARHopAsNumber'),
            'strictOrLoose' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelARHopStrictOrLoose'),
            'routerId' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelARHopRouterId'),
            'isLocalProtected' => MatchFilter::make()->setType('bool')->setColumn('localProtected'),
            'isLinkProtectionInUse' => MatchFilter::make()->setType('bool')->setColumn('linkProtectionInUse'),
            'isBandwidthProtected' => MatchFilter::make()->setType('bool')->setColumn('bandwidthProtected'),
            'isNextNodeProtected' => MatchFilter::make()->setType('bool')->setColumn('nextNodeProtected'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'listIndex' => SortableFilter::make()->setColumn('mplsTunnelARHopListIndex'),
            'index' => SortableFilter::make()->setColumn('mplsTunnelARHopIndex'),
            'addressCategory' => SortableFilter::make()->setColumn('mplsTunnelARHopAddrType'),
            'ipv4Address' => SortableFilter::make()->setColumn('mplsTunnelARHopIpv4Addr'),
            'ipv6Address' => SortableFilter::make()->setColumn('mplsTunnelARHopIpv6Addr'),
            'asNumber' => SortableFilter::make()->setColumn('mplsTunnelARHopAsNumber'),
            'strictOrLoose' => SortableFilter::make()->setColumn('mplsTunnelARHopStrictOrLoose'),
            'routerId' => SortableFilter::make()->setColumn('mplsTunnelARHopRouterId'),
            'isLocalProtected' => SortableFilter::make()->setColumn('localProtected'),
            'isLinkProtectionInUse' => SortableFilter::make()->setColumn('linkProtectionInUse'),
            'isBandwidthProtected' => SortableFilter::make()->setColumn('bandwidthProtected'),
            'isNextNodeProtected' => SortableFilter::make()->setColumn('nextNodeProtected'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('listIndex', fn ($value, $model) => $model->mplsTunnelARHopListIndex)->readonly(),
            field('index', fn ($value, $model) => $model->mplsTunnelARHopIndex)->readonly(),
            field('addressCategory', fn ($value, $model) => $model->mplsTunnelARHopAddrType)->readonly(),
            field('ipv4Address', fn ($value, $model) => $model->mplsTunnelARHopIpv4Addr)->readonly(),
            field('ipv6Address', fn ($value, $model) => $model->mplsTunnelARHopIpv6Addr)->readonly(),
            field('asNumber', fn ($value, $model) => $model->mplsTunnelARHopAsNumber)->readonly(),
            field('strictOrLoose', fn ($value, $model) => $model->mplsTunnelARHopStrictOrLoose)->readonly(),
            field('routerId', fn ($value, $model) => $model->mplsTunnelARHopRouterId)->readonly(),
            field('isLocalProtected', fn ($value, $model) => $model->localProtected)->readonly(),
            field('isLinkProtectionInUse', fn ($value, $model) => $model->linkProtectionInUse)->readonly(),
            field('isBandwidthProtected', fn ($value, $model) => $model->bandwidthProtected)->readonly(),
            field('isNextNodeProtected', fn ($value, $model) => $model->nextNodeProtected)->readonly(),
        ];
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
