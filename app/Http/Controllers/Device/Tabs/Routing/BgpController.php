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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Util\IP;
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

        $allPeers = $device->bgppeers()
            ->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get();

        if ($allPeers->isEmpty()) {
            return view('device.tabs.routing.bgp', [
                'device' => $device,
                'view' => $view,
                'local_as' => $device->bgpLocalAs,
                'bgp_menu' => $this->buildMenu($device, [], false),
                'peers' => collect(),
            ]);
        }

        $peers = match ($view) {
            'prefixes_ipv4unicast' => $allPeers->reject(fn (BgpPeer $p) => str_contains($p->bgpPeerIdentifier, ':')),
            'prefixes_ipv6unicast', 'prefixes_ipv6vpn' => $allPeers->filter(fn (BgpPeer $p) => str_contains($p->bgpPeerIdentifier, ':')),
            default => $allPeers,
        };

        $cbgp = $device->bgpPeersCbgp()->get();
        $activeAfis = $cbgp->map(fn ($c) => $c->afi . $c->safi)->unique()->all();
        $cbgpGrouped = $cbgp->groupBy('bgpPeerIdentifier');

        $ipv4Identifiers = $allPeers->pluck('bgpPeerIdentifier')
            ->filter(fn ($ip) => ! str_contains($ip, ':'))
            ->values()
            ->all();

        $macAccountingIds = empty($ipv4Identifiers) ? [] : DB::table('ipv4_mac')
            ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
            ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
            ->where('ports.device_id', $device->device_id)
            ->whereIn('ipv4_mac.ipv4_address', $ipv4Identifiers)
            ->pluck('mac_accounting.ma_id', 'ipv4_mac.ipv4_address')
            ->all();

        return view('device.tabs.routing.bgp', [
            'device' => $device,
            'view' => $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_menu' => $this->buildMenu($device, $activeAfis, ! empty($macAccountingIds)),
            'peers' => $this->formatPeers($device, $peers, $view, $cbgpGrouped, $macAccountingIds),
        ]);
    }

    /**
     * @param  array<string>  $activeAfis
     * @return array<int|string, array<int, array<string, string>>>
     */
    private function buildMenu(Device $device, array $activeAfis, bool $hasMacAccounting): array
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
     * @param  Collection<int, BgpPeer>  $peers
     * @param  Collection<array-key, EloquentCollection<int, \App\Models\BgpPeerCbgp>>  $cbgpGrouped
     * @param  array<string, int>  $macAccountingIds
     * @return Collection<int, array<string, mixed>>
     */
    private function formatPeers(Device $device, Collection $peers, string $view, Collection $cbgpGrouped, array $macAccountingIds): Collection
    {
        $linkedPorts = $this->resolveLinkedPorts($peers);

        return $peers->map(fn (BgpPeer $peer) => $this->formatPeer($peer, $device, $view, $cbgpGrouped, $linkedPorts, $macAccountingIds));
    }

    /**
     * @param  Collection<array-key, EloquentCollection<int, \App\Models\BgpPeerCbgp>>  $cbgpGrouped
     * @param  array<string, Port>  $linkedPorts
     * @param  array<string, int>  $macAccountingIds
     * @return array<string, mixed>
     */
    private function formatPeer(BgpPeer $peer, Device $device, string $view, Collection $cbgpGrouped, array $linkedPorts, array $macAccountingIds): array
    {
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
    }

    /**
     * @param  Collection<int, BgpPeer>  $peers
     * @return array<string, Port>
     */
    private function resolveLinkedPorts(Collection $peers): array
    {
        $ports = [];

        $ipv4s = $peers->pluck('bgpPeerIdentifier')
            ->filter(fn ($ip) => ! str_contains($ip, ':'))
            ->all();

        if (! empty($ipv4s)) {
            $ipv4Addresses = Ipv4Address::whereIn('ipv4_address', $ipv4s)
                ->with('port.device')
                ->get();

            foreach ($ipv4Addresses as $ip) {
                if ($ip->port) {
                    $ports[$ip->ipv4_address] = $ip->port;
                }
            }
        }

        $ipv6s = [];
        foreach ($peers as $peer) {
            if (str_contains($peer->bgpPeerIdentifier, ':') && $parsed = IP::parse($peer->bgpPeerIdentifier, true)) {
                $ipv6s[$parsed->uncompressed()] = $peer->bgpPeerIdentifier;
            }
        }

        if (! empty($ipv6s)) {
            $ipv6Addresses = Ipv6Address::whereIn('ipv6_address', array_keys($ipv6s))
                ->with('port.device')
                ->get();

            foreach ($ipv6Addresses as $ip) {
                if ($ip->port && isset($ipv6s[$ip->ipv6_address])) {
                    $ports[$ipv6s[$ip->ipv6_address]] = $ip->port;
                }
            }
        }

        return $ports;
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
        $isPrivate = ($as >= 64512 && $as <= 65534) || ($as >= 4200000000 && $as <= 4294967294);

        return $isPrivate ? ['Priv eBGP', 'text-info'] : ['eBGP', 'text-success'];
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
            $afisafi = substr($view, 9);
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
