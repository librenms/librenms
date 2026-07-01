<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Models\BgpPeer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Url;

class RoutingSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $bgp = BgpPeer::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('astext', 'like', $like)
                ->orWhere('bgpPeerDescr', 'like', $like)
                ->orWhere('bgpPeerIdentifier', 'like', $like)
                ->orWhere('bgpPeerRemoteAs', 'like', $like))
            ->orderBy('astext')->limit($limit)->get()
            ->map(fn (BgpPeer $b) => [
                'name' => $b->bgpPeerIdentifier,
                'subtitle' => trim($b->device?->display . ' AS' . $b->bgpPeerRemoteAs . ' ' . $b->astext),
                'icon' => 'fa fa-share-alt',
                'status' => match (true) {
                    $b->bgpPeerAdminStatus !== 'start' => 'tw:border-l-black!',
                    $b->bgpPeerState !== 'established' => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::deviceUrl($b->device, ['tab' => 'routing', 'proto' => 'bgp']),
            ]);

        return [$this->group('bgp', __('BGP Sessions'), $bgp)];
    }
}
