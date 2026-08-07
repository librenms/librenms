<?php

namespace App\Http\Controllers\Ajax\Search;

use App\Facades\LibrenmsConfig;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

class FdbSearchController extends GroupedSearchController
{
    protected function groups(string $search, string $like, int $limit, ?User $user): array
    {
        if (! LibrenmsConfig::get('webui.global_search.fdb')) {
            return [null];
        }

        $mac = strtolower(str_replace([':', '-', '.'], '', $search));
        $isIp = IP::isValid($search) || preg_match('/^[0-9]+\.[0-9]/', $search);
        $isMac = ! $isIp && ctype_xdigit($mac) && $mac !== '';

        // A port with exactly 1 MAC address in the FDB is directly connected to a single endpoint.
        $macCountSubquery = DB::table('ports_fdb')
            ->select('port_id', DB::raw('COUNT(*) as mac_count'))
            ->groupBy('port_id');

        $fdb = PortsFdb::hasAccess($user)
            ->select(['ports_fdb.ports_fdb_id', 'ports_fdb.mac_address', 'ports_fdb.port_id', 'ports_fdb.device_id', 'ports_fdb.vlan_id', 'mac_counts.mac_count'])
            ->joinSub($macCountSubquery, 'mac_counts', function ($join): void {
                $join->on('ports_fdb.port_id', '=', 'mac_counts.port_id');
            })
            ->with([
                'device:device_id,display,hostname,os',
                'port:port_id,device_id,ifDescr,ifName,ifAlias,ifIndex',
                'ipv4Addresses:mac_address,ipv4_address',
            ])
            ->where(function (Builder $q) use ($like, $mac, $isMac, $isIp): void {
                if ($isMac) {
                    // MAC search: match directly against the FDB mac_address column
                    $q->where('ports_fdb.mac_address', 'like', '%' . $mac . '%');
                } elseif ($isIp) {
                    // IP search: join ipv4_mac to find which MACs belong to that IP,
                    // then match FDB entries for those MACs so we show where the endpoint is plugged in
                    $q->whereExists(function ($sub) use ($like): void {
                        $sub->from('ipv4_mac')
                            ->whereColumn('ipv4_mac.mac_address', 'ports_fdb.mac_address')
                            ->where('ipv4_mac.ipv4_address', 'like', $like);
                    });
                } else {
                    $q->where('ports_fdb.mac_address', 'like', $like);
                }
            })
            ->orderBy('mac_counts.mac_count') // single-MAC ports (direct connections) first
            ->orderBy('ports_fdb.mac_address')
            ->limit($limit)
            ->get()
            ->map(function (PortsFdb $f): array {
                $macCount = (int) $f->mac_count;
                $portLabel = $f->port?->getFullLabel();
                $deviceDisplay = $f->device?->display ?? $f->device?->hostname ?? '';
                $connectionHint = $macCount === 1
                    ? __('search.fdb_connected')
                    : __('search.fdb_trunk');

                $formattedMac = Mac::parse($f->mac_address)->readable();
                $ipAddress = $f->ipv4Addresses->first()?->ipv4_address;

                return [
                    'name' => $ipAddress ? $formattedMac . ' (' . $ipAddress . ')' : $formattedMac,
                    'subtitle' => implode(' · ', array_filter([
                        $deviceDisplay,
                        $portLabel,
                        $connectionHint,
                    ])),
                    'icon' => 'fa fa-plug',
                    'status' => $macCount === 1 ? 'tw:border-l-green-600!' : null,
                    'url' => Url::generate(['page' => 'search', 'search' => 'fdb', 'searchPhrase' => $f->mac_address]),
                ];
            });

        return [$fdb->isEmpty() ? null : ['type' => 'fdb_tables', 'label' => __('search.fdb_tables'), 'results' => $fdb]];
    }
}
