<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\BgpPeer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Url;

class RoutingSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.routing')) {
            return [null];
        }

        $bgp = BgpPeer::hasAccess($user)->with('device')
            ->where(fn (Builder $q) => $q->where('astext', 'like', $like)
                ->orWhere('bgpPeerDescr', 'like', $like)
                ->orWhere('bgpPeerIdentifier', 'like', $like)
                ->orWhere('bgpPeerRemoteAs', 'like', $like))
            ->orderBy('astext')->limit($limit)->get()
            ->map(fn (BgpPeer $bgpPeer) => [
                'name' => $bgpPeer->bgpPeerIdentifier,
                'subtitle' => implode(' · ', array_filter([
                    $bgpPeer->device?->display,
                    'AS' . $bgpPeer->bgpPeerRemoteAs,
                    $bgpPeer->astext,
                ])),
                'icon' => 'fa fa-share-alt',
                'status' => match (true) {
                    $bgpPeer->bgpPeerAdminStatus !== 'start' => 'tw:border-l-black!',
                    $bgpPeer->bgpPeerState !== 'established' => 'tw:border-l-red-600!',
                    default => 'tw:border-l-green-600!',
                },
                'url' => Url::deviceUrl($bgpPeer->device, ['tab' => 'routing', 'proto' => 'bgp']),
            ]);

        return [$bgp->isEmpty() ? null : ['type' => 'bgp', 'label' => __('search.bgp_sessions'), 'results' => $bgp]];
    }
}
