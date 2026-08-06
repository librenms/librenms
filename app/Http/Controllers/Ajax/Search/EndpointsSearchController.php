<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Url;

class EndpointsSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        $mac = strtolower(str_replace([':', '-', '.'], '', $search));
        $isMac = ctype_xdigit($mac) && $mac !== '';

        $fdb = PortsFdb::hasAccess($user)->with(['device', 'port'])
            ->where(function (Builder $q) use ($like, $mac, $isMac) {
                if ($isMac) {
                    $q->where('mac_address', 'like', '%' . $mac . '%');
                } else {
                    $q->where('mac_address', 'like', $like);
                }
            })
            ->limit($limit)->get()
            ->map(fn (PortsFdb $f) => [
                'name' => $f->mac_address,
                'subtitle' => trim($f->device?->display . ' ' . $f->port?->getLabel() . ' (FDB)'),
                'icon' => 'fa fa-microchip',
                'url' => Url::deviceUrl($f->device, ['tab' => 'ports', 'view' => 'fdb', 'search' => $f->mac_address]),
            ]);

        $arp = Ipv4Mac::hasAccess($user)->with(['device', 'port'])
            ->where(function (Builder $q) use ($like, $mac, $isMac) {
                $q->where('ipv4_address', 'like', $like);
                if ($isMac) {
                    $q->orWhere('mac_address', 'like', '%' . $mac . '%');
                } else {
                    $q->orWhere('mac_address', 'like', $like);
                }
            })
            ->limit($limit)->get()
            ->map(fn (Ipv4Mac $a) => [
                'name' => $a->ipv4_address,
                'subtitle' => trim($a->device?->display . ' ' . $a->port?->getLabel() . ' ' . $a->mac_address . ' (ARP)'),
                'icon' => 'fa fa-address-card',
                'url' => Url::deviceUrl($a->device, ['tab' => 'ports', 'view' => 'arp', 'search' => $a->ipv4_address]),
            ]);

        $ndp = Ipv6Nd::hasAccess($user)->with(['device', 'port'])
            ->where(function (Builder $q) use ($like, $mac, $isMac) {
                $q->where('ipv6_address', 'like', $like);
                if ($isMac) {
                    $q->orWhere('mac_address', 'like', '%' . $mac . '%');
                } else {
                    $q->orWhere('mac_address', 'like', $like);
                }
            })
            ->limit($limit)->get()
            ->map(fn (Ipv6Nd $n) => [
                'name' => $n->ipv6_address,
                'subtitle' => trim($n->device?->display . ' ' . $n->port?->getLabel() . ' ' . $n->mac_address . ' (NDP)'),
                'icon' => 'fa fa-address-card',
                'url' => Url::deviceUrl($n->device, ['tab' => 'ports', 'view' => 'nd', 'search' => $n->ipv6_address]),
            ]);

        $results = $fdb->concat($arp)->concat($ndp)->take($limit);

        return [$results->isEmpty() ? null : ['type' => 'endpoints', 'label' => __('Endpoints'), 'results' => $results]];
    }
}
