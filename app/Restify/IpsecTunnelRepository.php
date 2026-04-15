<?php

namespace App\Restify;

use App\Models\IpsecTunnel;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class IpsecTunnelRepository extends Repository
{
    public static string $model = IpsecTunnel::class;

    public static string $id = 'tunnel_id';

    public static string $title = 'tunnel_name';




    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('tunnel_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'peerPort' => MatchFilter::make()->setType('integer')->setColumn('peer_port'),
            'peerAddress' => MatchFilter::make()->setType('text')->setColumn('peer_addr'),
            'localAddress' => MatchFilter::make()->setType('text')->setColumn('local_addr'),
            'localPort' => MatchFilter::make()->setType('integer')->setColumn('local_port'),
            'name' => MatchFilter::make()->setType('text')->setColumn('tunnel_name'),
            'status' => MatchFilter::make()->setType('text')->setColumn('tunnel_status'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'peerPort' => SortableFilter::make()->setColumn('peer_port'),
            'peerAddress' => SortableFilter::make()->setColumn('peer_addr'),
            'localAddress' => SortableFilter::make()->setColumn('local_addr'),
            'localPort' => SortableFilter::make()->setColumn('local_port'),
            'name' => SortableFilter::make()->setColumn('tunnel_name'),
            'status' => SortableFilter::make()->setColumn('tunnel_status'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('peerPort', fn ($value, $model) => $model->peer_port)->readonly(),
            field('peerAddress', fn ($value, $model) => $model->peer_addr)->readonly(),
            field('localAddress', fn ($value, $model) => $model->local_addr)->readonly(),
            field('localPort', fn ($value, $model) => $model->local_port)->readonly(),
            field('name', fn ($value, $model) => $model->tunnel_name)->readonly(),
            field('status', fn ($value, $model) => $model->tunnel_status)->readonly(),
        ];
    }

    /**
     * IpsecTunnel extends plain Model (no hasAccess scope), so we filter by device access on device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', IpsecTunnel::class)) {
                return $query;
            }

            return $query->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * IPsec tunnels are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPsec tunnels are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
