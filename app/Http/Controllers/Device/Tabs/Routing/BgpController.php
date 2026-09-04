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
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
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

        Validator::validate($request->all(), [
            'view' => 'nullable|in:' . implode(',', $validViews),
        ]);

        $view = $request->query('view', 'basic');

        $bgpMenu = [
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

        $availablePrefixViews = [
            'ipv4unicast' => [
                'name' => __('IPv4 Ucast'),
                'url' => 'prefixes_ipv4unicast',
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv4unicast']),
            ],
            'ipv4vpn' => [
                'name' => __('VPNv4 Ucast'),
                'url' => 'prefixes_ipv4vpn',
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv4vpn']),
            ],
            'ipv6unicast' => [
                'name' => __('IPv6 Ucast'),
                'url' => 'prefixes_ipv6unicast',
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv6unicast']),
            ],
            'ipv6vpn' => [
                'name' => __('VPNv6 Ucast'),
                'url' => 'prefixes_ipv6vpn',
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv6vpn']),
            ],
        ];

        $activeAfis = DB::table('bgpPeers_cbgp')
            ->where('device_id', $device->device_id)
            ->selectRaw('CONCAT(afi, safi) as afisafi')
            ->distinct()
            ->pluck('afisafi')
            ->toArray();

        $prefixMenu = [];
        foreach ($availablePrefixViews as $afisafi => $item) {
            if (in_array($afisafi, $activeAfis, true)) {
                $prefixMenu[] = $item;
            }
        }

        if (! empty($prefixMenu)) {
            $bgpMenu[__('Prefixes')] = $prefixMenu;
        }

        $hasMacAccounting = DB::table('ipv4_mac')
            ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
            ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
            ->where('ports.device_id', $device->device_id)
            ->whereIn('ipv4_mac.ipv4_address', $device->bgppeers()->select('bgpPeerIdentifier'))
            ->exists();

        if ($hasMacAccounting) {
            $bgpMenu[__('Traffic')] = [
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

        return view('device.tabs.routing.bgp', [
            'device' => $device,
            'view' => $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_menu' => $bgpMenu,
            'peers' => $this->getPeers($device, $view),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getPeers(Device $device, string $view): Collection
    {
        return $device->bgppeers()
            ->when($view === 'prefixes_ipv4unicast', fn ($q) => $q->where('bgpPeerIdentifier', 'not like', '%:%'))
            ->when(in_array($view, ['prefixes_ipv6unicast', 'prefixes_ipv6vpn']), fn ($q) => $q->where('bgpPeerIdentifier', 'like', '%:%'))
            ->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get()
            ->map(fn (BgpPeer $peer) => $this->formatPeer($peer, $device, $view));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPeer(BgpPeer $peer, Device $device, string $view): array
    {
        $peerIdentifierIp = IP::parse($peer->bgpPeerIdentifier, true);

        [$peerType, $peerTypeClass] = $this->determinePeerType($peer->bgpPeerRemoteAs, $device->bgpLocalAs);
        $linkedPort = $this->resolveLinkedPort($peer->bgpPeerIdentifier, $peerIdentifierIp);

        $cbgpList = BgpPeerCbgp::where('device_id', $device->device_id)
            ->where('bgpPeerIdentifier', $peer->bgpPeerIdentifier)
            ->get();
        $afiList = $cbgpList->map(fn ($c) => $c->afi . '.' . $c->safi)->implode(', ');
        $afisafiMap = $cbgpList->mapWithKeys(fn ($c) => [$c->afi . $c->safi => true])->all();

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
            'linked_port' => $linkedPort,
            'peer_type' => $peerType,
            'peer_type_class' => $peerTypeClass,
            ...$this->resolveGraphSettings($peer, $device, $view, $afisafiMap),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function determinePeerType(int|string|null $remoteAs, int|string|null $localAs): array
    {
        if ($remoteAs == $localAs) {
            return ['iBGP', 'text-primary'];
        }

        $remoteAsInt = (int) $remoteAs;
        if (($remoteAsInt >= 64512 && $remoteAsInt <= 65534) || ($remoteAsInt >= 4200000000 && $remoteAsInt <= 4294967294)) {
            return ['Priv eBGP', 'text-info'];
        }

        return ['eBGP', 'text-success'];
    }

    private function resolveLinkedPort(string $identifier, ?IP $peerIdentifierIp): mixed
    {
        $ipv4Host = Ipv4Address::where('ipv4_address', $identifier)
            ->with('port.device')
            ->first()
            ?->port;

        if ($ipv4Host) {
            return $ipv4Host;
        }

        if ($peerIdentifierIp) {
            return Ipv6Address::where('ipv6_address', $peerIdentifierIp->uncompressed())
                ->with('port.device')
                ->first()
                ?->port;
        }

        return null;
    }

    private function formatLastError(BgpPeer $peer): string
    {
        $lastError = '';
        if ($peer->bgpPeerLastErrorCode != 0 || $peer->bgpPeerLastErrorSubCode != 0) {
            $lastError = Rewrite::bgpErrorCode($peer->bgpPeerLastErrorCode, $peer->bgpPeerLastErrorSubCode);
        }

        if ($peer->bgpPeerLastErrorText) {
            $lastError = trim($lastError . ' ' . $peer->bgpPeerLastErrorText);
        }

        return $lastError;
    }

    /**
     * @param  array<string, bool>  $afisafiMap
     * @return array{show_graph: bool, graph_type: string, graph_id: int}
     */
    private function resolveGraphSettings(BgpPeer $peer, Device $device, string $view, array $afisafiMap): array
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
            $maId = DB::table('ipv4_mac')
                ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
                ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
                ->where('ipv4_mac.ipv4_address', $peer->bgpPeerIdentifier)
                ->where('ports.device_id', $device->device_id)
                ->value('mac_accounting.ma_id');

            if ($maId !== null) {
                return ['show_graph' => true, 'graph_type' => $view, 'graph_id' => (int) $maId];
            }
        }

        return ['show_graph' => false, 'graph_type' => "bgp_$view", 'graph_id' => $peer->bgpPeer_id];
    }
}
