<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

class ArpSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.endpoints')) {
            return [null];
        }

        $mac = strtolower(str_replace([':', '-', '.'], '', $search));
        $isIp = IP::isValid($search) || preg_match('/^[0-9]+\.[0-9]/', $search);
        // A valid IP can strip to hex chars (e.g. 10.0.0.1 → 10001), so only treat as MAC when not an IP
        $isMac = ! $isIp && ctype_xdigit($mac) && $mac !== '';

        $groups = $this->groupedQuery(Ipv4Mac::hasAccess($user), 'ipv4_mac', 'ipv4_address', 'arp', $like, $mac, $isMac, $limit)
            ->unionAll($this->groupedQuery(Ipv6Nd::hasAccess($user), 'ipv6_nd', 'ipv6_address', 'nd', $like, $mac, $isMac, $limit))
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
     * Grouped, aliased query so ipv4_mac and ipv6_nd rows come back in an identical shape
     * (address, mac_address, kind, total, devices_count, ports_count, sample_device_id, sample_port_id).
     */
    private function groupedQuery(Builder $query, string $table, string $addressColumn, string $kind, string $like, string $mac, bool $isMac, int $limit): Builder
    {
        return $query
            ->where(function (Builder $q) use ($table, $addressColumn, $like, $mac, $isMac): void {
                $q->where("$table.$addressColumn", 'like', $like);
                if ($isMac) {
                    $q->orWhere("$table.mac_address", 'like', '%' . $mac . '%');
                }
            })
            ->select([
                "$table.$addressColumn as address",
                "$table.mac_address as mac_address",
                DB::raw("'$kind' as kind"),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT device_id) as devices_count'),
                DB::raw('COUNT(DISTINCT port_id) as ports_count'),
                DB::raw('MIN(port_id) as sample_port_id'),
                DB::raw('MIN(device_id) as sample_device_id'),
            ])
            ->groupBy("$table.$addressColumn", "$table.mac_address")
            ->limit($limit);
    }

    private function formatResult(object $g, ?Device $device, ?Port $port): array
    {
        if ($g->total === 1) {
            $deviceDisplay = $device?->display ?? $device?->hostname ?? '';
            $subtitle = implode(' · ', array_filter([$deviceDisplay, $port?->getFullLabel()]));
        } else {
            $subtitle = trans_choice('search.arp_summary', $g->total, [
                'count' => $g->total,
                'devices' => $g->devices_count,
                'ports' => $g->ports_count,
            ]);
        }

        $name = $g->kind === 'arp'
            ? Mac::parse($g->mac_address)->readable() . ' (' . $g->address . ')'
            : $g->address . ' (' . Mac::parse($g->mac_address)->readable() . ')';

        return [
            'name' => $name,
            'subtitle' => $subtitle,
            'icon' => 'fa fa-map-marker',
            'url' => Url::deviceUrl($device, ['tab' => 'ports', 'view' => $g->kind === 'arp' ? 'arp' : 'nd', 'search' => $g->address]),
        ];
    }
}
