<?php

namespace App\Restify;

use App\Models\MplsTunnelCHop;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MplsTunnelCHopRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = MplsTunnelCHop::class;

    public static string $id = 'c_hop_id';

    public static string $title = 'mplsTunnelCHopIpv4Addr';




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
            'listIndex' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelCHopListIndex'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelCHopIndex'),
            'addressCategory' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelCHopAddrType'),
            'ipv4Address' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelCHopIpv4Addr'),
            'ipv6Address' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelCHopIpv6Addr'),
            'asNumber' => MatchFilter::make()->setType('integer')->setColumn('mplsTunnelCHopAsNumber'),
            'strictOrLoose' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelCHopStrictOrLoose'),
            'routerId' => MatchFilter::make()->setType('text')->setColumn('mplsTunnelCHopRouterId'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'listIndex' => SortableFilter::make()->setColumn('mplsTunnelCHopListIndex'),
            'index' => SortableFilter::make()->setColumn('mplsTunnelCHopIndex'),
            'addressCategory' => SortableFilter::make()->setColumn('mplsTunnelCHopAddrType'),
            'ipv4Address' => SortableFilter::make()->setColumn('mplsTunnelCHopIpv4Addr'),
            'ipv6Address' => SortableFilter::make()->setColumn('mplsTunnelCHopIpv6Addr'),
            'asNumber' => SortableFilter::make()->setColumn('mplsTunnelCHopAsNumber'),
            'strictOrLoose' => SortableFilter::make()->setColumn('mplsTunnelCHopStrictOrLoose'),
            'routerId' => SortableFilter::make()->setColumn('mplsTunnelCHopRouterId'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('listIndex', fn ($value, $model) => $model->mplsTunnelCHopListIndex)->readonly(),
            field('index', fn ($value, $model) => $model->mplsTunnelCHopIndex)->readonly(),
            field('addressCategory', fn ($value, $model) => $model->mplsTunnelCHopAddrType)->readonly(),
            field('ipv4Address', fn ($value, $model) => $model->mplsTunnelCHopIpv4Addr)->readonly(),
            field('ipv6Address', fn ($value, $model) => $model->mplsTunnelCHopIpv6Addr)->readonly(),
            field('asNumber', fn ($value, $model) => $model->mplsTunnelCHopAsNumber)->readonly(),
            field('strictOrLoose', fn ($value, $model) => $model->mplsTunnelCHopStrictOrLoose)->readonly(),
            field('routerId', fn ($value, $model) => $model->mplsTunnelCHopRouterId)->readonly(),
        ];
    }

    /**
     * MPLS tunnel C hops are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * MPLS tunnel C hops are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
