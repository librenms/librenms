<?php

/**
 * BgpController.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Time;

class BgpController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $validViews = [
            'basic',
            'updates',
            'prefixes_ipv4unicast',
            'prefixes_ipv4vpn',
            'prefixes_ipv6unicast',
            'prefixes_ipv6vpn',
            'macaccounting_bits',
            'macaccounting_pkts',
        ];

        $request->validate([
            'view' => 'nullable|in:' . implode(',', $validViews),
        ]);

        $view = $request->query('view', 'basic');

        return view('device.tabs.routing.bgp', [
            'device' => $device,
            'view' => $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_menu' => $this->buildMenu($device),
            'peers' => $this->getPeers($device, $view),
        ]);
    }

    /**
     * @return array<int|string, array<int, array<string, string>>>
     */
    private function buildMenu(Device $device): array
    {
        $menu = [
            [
                [
                    'name' => __('Basic'),
                    'url' => 'basic',
                    'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'basic']),
                ],
                [
                    'name' => __('Updates'),
                    'url' => 'updates',
                    'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'updates']),
                ],
            ],
        ];

        $activeAfis = $device->bgpPeersCbgp()
            ->selectRaw('CONCAT(afi, safi) as afisafi')
            ->distinct()
            ->pluck('afisafi')
            ->all();

        $prefixViews = [
            'ipv4unicast' => __('IPv4 Ucast'),
            'ipv4vpn' => __('VPNv4 Ucast'),
            'ipv6unicast' => __('IPv6 Ucast'),
            'ipv6vpn' => __('VPNv6 Ucast'),
        ];

        $prefixItems = [];
        foreach ($prefixViews as $afisafi => $name) {
            if (in_array($afisafi, $activeAfis, true)) {
                $prefixItems[] = [
                    'name' => $name,
                    'url' => "prefixes_$afisafi",
                    'link' => route('device.routing.bgp', ['device' => $device, 'view' => "prefixes_$afisafi"]),
                ];
            }
        }

        if (! empty($prefixItems)) {
            $menu[__('Prefixes')] = $prefixItems;
        }

        $hasMacAccounting = DB::table('ipv4_mac')
            ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
            ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
            ->where('ports.device_id', $device->device_id)
            ->whereIn('ipv4_mac.ipv4_address', $device->bgppeers()->select('bgpPeerIdentifier'))
            ->exists();

        if ($hasMacAccounting) {
            $menu[__('Traffic')] = [
                [
                    'name' => __('Bits'),
                    'url' => 'macaccounting_bits',
                    'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'macaccounting_bits']),
                ],
                [
                    'name' => __('Packets'),
                    'url' => 'macaccounting_pkts',
                    'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'macaccounting_pkts']),
                ],
            ];
        }

        return $menu;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getPeers(Device $device, string $view): Collection
    {
        $peers = $device->bgppeers()
            ->when($view === 'prefixes_ipv4unicast', fn ($q) => $q->where('bgpPeerIdentifier', 'not like', '%:%'))
            ->when(in_array($view, ['prefixes_ipv6unicast', 'prefixes_ipv6vpn'], true), fn ($q) => $q->where('bgpPeerIdentifier', 'like', '%:%'))
            ->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get();

        if ($peers->isEmpty()) {
            return collect();
        }

        $cbgpGrouped = $device->bgpPeersCbgp()
            ->whereIn('bgpPeerIdentifier', $peers->pluck('bgpPeerIdentifier'))
            ->get()
            ->groupBy('bgpPeerIdentifier');

        $linkedPorts = $this->resolveLinkedPorts($peers);
        $macAccountingIds = $this->resolveMacAccountingIds($device, $view, $peers);

        return $peers->map(function (BgpPeer $peer) use ($device, $view, $cbgpGrouped, $linkedPorts, $macAccountingIds) {
            $peerCbgp = $cbgpGrouped->get($peer->bgpPeerIdentifier, collect());
            $peerIdentifierIp = IP::parse($peer->bgpPeerIdentifier, true);

            [$peerType, $peerTypeClass] = $this->determinePeerType($peer->bgpPeerRemoteAs, $device->bgpLocalAs);

            $afiList = $peerCbgp->map(fn ($c) => "$c->afi.$c->safi")->implode(', ');
            $afisafiMap = array_fill_keys($peerCbgp->map(fn ($c) => $c->afi . $c->safi)->all(), true);

            return [
                'peer' => $peer,
                'identifier_compressed' => $peerIdentifierIp?->compressed() ?: $peer->bgpPeerIdentifier,
                'remote_as' => $peer->bgpPeerRemoteAs,
                'astext' => $peer->astext,
                'descr' => $peer->bgpPeerDescr,
                'admin_status' => $peer->bgpPeerAdminStatus,
                'admin_color' => in_array($peer->bgpPeerAdminStatus, ['start', 'running'], true) ? 'success' : 'default',
                'state' => $peer->bgpPeerState,
                'state_color' => $peer->bgpPeerState === 'established' ? 'success' : 'danger',
                'fsm_established_time' => Time::formatInterval($peer->bgpPeerFsmEstablishedTime),
                'in_updates' => $peer->bgpPeerInUpdates,
                'out_updates' => $peer->bgpPeerOutUpdates,
                'last_error' => $this->formatLastError($peer),
                'afi_list' => $afiList,
                'linked_port' => $linkedPorts[$peer->bgpPeerIdentifier] ?? null,
                'peer_type' => $peerType,
                'peer_type_class' => $peerTypeClass,
                ...$this->resolveGraphSettings($peer, $view, $afisafiMap, $macAccountingIds),
            ];
        });
    }

    /**
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, Port>
     */
    private function resolveLinkedPorts(Collection $peers): array
    {
        $ipv4s = [];
        $ipv6s = [];

        foreach ($peers as $peer) {
            $ip = IP::parse($peer->bgpPeerIdentifier, true);
            if ($ip instanceof IPv4) {
                $ipv4s[$peer->bgpPeerIdentifier] = $peer->bgpPeerIdentifier;
            } elseif ($ip instanceof IPv6) {
                $ipv6s[$peer->bgpPeerIdentifier] = $ip->uncompressed();
            }
        }

        $ports = [];

        if (! empty($ipv4s)) {
            $ipv4Ports = Ipv4Address::whereIn('ipv4_address', array_values($ipv4s))
                ->with('port.device')
                ->get()
                ->keyBy('ipv4_address');

            foreach ($ipv4s as $identifier => $ipStr) {
                if ($port = $ipv4Ports->get($ipStr)?->port) {
                    $ports[$identifier] = $port;
                }
            }
        }

        if (! empty($ipv6s)) {
            $ipv6Ports = Ipv6Address::whereIn('ipv6_address', array_values($ipv6s))
                ->with('port.device')
                ->get()
                ->keyBy('ipv6_address');

            foreach ($ipv6s as $identifier => $uncompressed) {
                if ($port = $ipv6Ports->get($uncompressed)?->port) {
                    $ports[$identifier] = $port;
                }
            }
        }

        return $ports;
    }

    /**
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, int>
     */
    private function resolveMacAccountingIds(Device $device, string $view, Collection $peers): array
    {
        if (! in_array($view, ['macaccounting_bits', 'macaccounting_pkts'], true)) {
            return [];
        }

        return DB::table('ipv4_mac')
            ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
            ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
            ->where('ports.device_id', $device->device_id)
            ->whereIn('ipv4_mac.ipv4_address', $peers->pluck('bgpPeerIdentifier'))
            ->pluck('mac_accounting.ma_id', 'ipv4_mac.ipv4_address')
            ->all();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function determinePeerType(int|string|null $remoteAs, int|string|null $localAs): array
    {
        if ($remoteAs == $localAs) {
            return ['iBGP', 'text-primary'];
        }

        $as = (int) $remoteAs;
        if (($as >= 64512 && $as <= 65534) || ($as >= 4200000000 && $as <= 4294967294)) {
            return ['Priv eBGP', 'text-info'];
        }

        return ['eBGP', 'text-success'];
    }

    private function formatLastError(BgpPeer $peer): string
    {
        $error = ($peer->bgpPeerLastErrorCode || $peer->bgpPeerLastErrorSubCode)
            ? Rewrite::bgpErrorCode($peer->bgpPeerLastErrorCode, $peer->bgpPeerLastErrorSubCode)
            : '';

        return trim("$error {$peer->bgpPeerLastErrorText}");
    }

    /**
     * @param  array<string, bool>  $afisafiMap
     * @param  array<string, int>  $macAccountingIds
     * @return array{show_graph: bool, graph_type: string, graph_id: int}
     */
    private function resolveGraphSettings(BgpPeer $peer, string $view, array $afisafiMap, array $macAccountingIds): array
    {
        if ($view === 'updates') {
            return ['show_graph' => true, 'graph_type' => 'bgp_updates', 'graph_id' => $peer->bgpPeer_id];
        }

        if (str_starts_with($view, 'prefixes_')) {
            $afisafi = substr($view, strlen('prefixes_'));
            if (! empty($afisafiMap[$afisafi])) {
                return ['show_graph' => true, 'graph_type' => "bgp_$view", 'graph_id' => $peer->bgpPeer_id];
            }
        }

        if (in_array($view, ['macaccounting_bits', 'macaccounting_pkts'], true)) {
            if ($maId = $macAccountingIds[$peer->bgpPeerIdentifier] ?? null) {
                return ['show_graph' => true, 'graph_type' => $view, 'graph_id' => (int) $maId];
            }
        }

        return ['show_graph' => false, 'graph_type' => "bgp_$view", 'graph_id' => $peer->bgpPeer_id];
    }
}
