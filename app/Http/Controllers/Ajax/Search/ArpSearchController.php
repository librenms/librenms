<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

class ArpSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.arp')) {
            return [null];
        }

        $mac = strtolower(str_replace([':', '-', '.'], '', $search));
        $isIp = IP::isValid($search) || preg_match('/^[0-9]+\.[0-9]/', $search);
        // A valid IP can strip to hex chars (e.g. 10.0.0.1 → 10001), so only treat as MAC when not an IP
        $isMac = ! $isIp && ctype_xdigit($mac);

        $arp = Ipv4Mac::hasAccess($user)
            ->where(function (Builder $q) use ($like, $mac, $isMac): void {
                $q->where('ipv4_mac.ipv4_address', 'like', $like);
                if ($isMac) {
                    $q->orWhere('ipv4_mac.mac_address', 'like', '%' . $mac . '%');
                }
            })
            ->select(['ipv4_mac.ipv4_address as address', 'ipv4_mac.mac_address as mac_address'])
            ->selectRaw("'arp' as kind, COUNT(*) as total, COUNT(DISTINCT device_id) as devices_count, COUNT(DISTINCT port_id) as ports_count, MIN(port_id) as sample_port_id, MIN(device_id) as sample_device_id")
            ->groupBy('ipv4_mac.ipv4_address', 'ipv4_mac.mac_address')
            ->limit($limit);

        $nd = Ipv6Nd::hasAccess($user)
            ->where(function (Builder $q) use ($like, $mac, $isMac): void {
                $q->where('ipv6_nd.ipv6_address', 'like', $like);
                if ($isMac) {
                    $q->orWhere('ipv6_nd.mac_address', 'like', '%' . $mac . '%');
                }
            })
            ->select(['ipv6_nd.ipv6_address as address', 'ipv6_nd.mac_address as mac_address'])
            ->selectRaw("'nd' as kind, COUNT(*) as total, COUNT(DISTINCT device_id) as devices_count, COUNT(DISTINCT port_id) as ports_count, MIN(port_id) as sample_port_id, MIN(device_id) as sample_device_id")
            ->groupBy('ipv6_nd.ipv6_address', 'ipv6_nd.mac_address')
            ->limit($limit);

        /** @var Collection<int, object{kind: string, address: string, mac_address: string, total: int, devices_count: int, ports_count: int, sample_device_id: int, sample_port_id: int}>  $groups */
        $groups = $arp->unionAll($nd)
            ->orderBy('address')
            ->limit($limit)
            ->get();

        if ($groups->isEmpty()) {
            return [null];
        }

        $devices = Device::whereIn('device_id', $groups->pluck('sample_device_id')->unique())
            ->get(['device_id', 'display', 'hostname', 'os'])
            ->keyBy('device_id');

        $ports = Port::whereIn('port_id', $groups->pluck('sample_port_id')->unique())
            ->get(['port_id', 'device_id', 'ifDescr', 'ifName', 'ifAlias', 'ifIndex'])
            ->keyBy('port_id');

        $results = $groups->map(fn ($g) => $this->formatResult($g, $devices->get($g->sample_device_id), $ports->get($g->sample_port_id)));

        return [['type' => 'arp_tables', 'label' => __('search.arp_tables'), 'results' => $results]];
    }

    /**
     * @param  object{kind: string, address: string, mac_address: string, total: int, devices_count: int, ports_count: int, sample_device_id: int, sample_port_id: int}  $g
     * @return array<string, mixed>
     */
    private function formatResult(object $g, ?Device $device, ?Port $port): array
    {
        if ($g->total === 1) {
            $deviceDisplay = $device?->display;
            $subtitle = implode(' · ', array_filter([$deviceDisplay, $port?->getFullLabel()]));
        } else {
            $subtitle = trans_choice('search.arp_summary', $g->total, [
                'count' => $g->total,
                'devices' => $g->devices_count,
                'ports' => $g->ports_count,
            ]);
        }

        $mac = Mac::parse($g->mac_address)->readable();
        $ip = IP::parse($g->address, true)?->compressed();

        $url = $g->kind === 'arp'
            ? Url::generate(['page' => 'search', 'search' => 'arp', 'searchPhrase' => $g->address])
            : Url::deviceUrl($device, ['tab' => 'ports', 'view' => 'nd']);

        return [
            'name' => "$ip ($mac)",
            'subtitle' => $subtitle,
            'icon' => 'fa fa-map-marker',
            'url' => $url,
        ];
    }
}
