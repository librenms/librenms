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
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use LibreNMS\Util\IP;
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

        $bgpOptions = [
            'basic' => [
                'text' => __('Basic'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'basic']),
            ],
            'updates' => [
                'text' => __('Updates'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'updates']),
            ],
            'prefixes_ipv4unicast' => [
                'text' => __('IPv4 Ucast'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv4unicast']),
            ],
            'prefixes_ipv4vpn' => [
                'text' => __('VPNv4 Ucast'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv4vpn']),
            ],
            'prefixes_ipv6unicast' => [
                'text' => __('IPv6 Ucast'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv6vpn']),
            ],
            'prefixes_ipv6vpn' => [
                'text' => __('VPNv6 Ucast'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'prefixes_ipv6vpn']),
            ],
            'macaccounting_bits' => [
                'text' => __('Bits'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'macaccounting_bits']),
            ],
            'macaccounting_pkts' => [
                'text' => __('Packets'),
                'link' => route('device.routing.bgp', ['device' => $device, 'view' => 'macaccounting_pkts']),
            ],
        ];

        $query = $device->bgppeers();
        if ($view === 'prefixes_ipv4unicast') {
            $query->where('bgpPeerIdentifier', 'not like', '%:%');
        } elseif ($view === 'prefixes_ipv6unicast' || $view === 'prefixes_ipv6vpn') {
            $query->where('bgpPeerIdentifier', 'like', '%:%');
        }

        $peers = $query->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get()
            ->map(function ($peer) use ($device, $view) {
                $peerIdentifierIp = IP::parse($peer->bgpPeerIdentifier, true);

                $peerType = 'eBGP';
                $peerTypeClass = 'text-success';
                if ($peer->bgpPeerRemoteAs == $device->bgpLocalAs) {
                    $peerType = 'iBGP';
                    $peerTypeClass = 'text-primary';
                } elseif (
                    ($peer->bgpPeerRemoteAs >= 64512 && $peer->bgpPeerRemoteAs <= 65534)
                    || ($peer->bgpPeerRemoteAs >= 4200000000 && $peer->bgpPeerRemoteAs <= 4294967294)
                ) {
                    $peerType = 'Priv eBGP';
                    $peerTypeClass = 'text-info';
                }

                $ipv4Host = Ipv4Address::where('ipv4_address', $peer->bgpPeerIdentifier)
                    ->with('port.device')
                    ->first()
                    ?->port;

                $ipv6Host = null;
                if ($peerIdentifierIp) {
                    $ipv6Host = Ipv6Address::where('ipv6_address', $peerIdentifierIp->uncompressed())
                        ->with('port.device')
                        ->first()
                        ?->port;
                }

                $linkedPort = $ipv4Host ?: $ipv6Host;

                $cbgpList = BgpPeerCbgp::where('device_id', $device->device_id)
                    ->where('bgpPeerIdentifier', $peer->bgpPeerIdentifier)
                    ->get();
                $afiList = $cbgpList->map(fn ($c) => $c->afi . '.' . $c->safi)->implode(', ');
                $afisafiMap = $cbgpList->mapWithKeys(fn ($c) => [$c->afi . $c->safi => true])->all();

                $lastError = '';
                if ($peer->bgpPeerLastErrorCode != 0 || $peer->bgpPeerLastErrorSubCode != 0) {
                    if (function_exists('describe_bgp_error_code')) {
                        $lastError = describe_bgp_error_code($peer->bgpPeerLastErrorCode, $peer->bgpPeerLastErrorSubCode);
                    }
                }
                if ($peer->bgpPeerLastErrorText) {
                    $lastError = trim($lastError . ' ' . $peer->bgpPeerLastErrorText);
                }

                $showGraph = false;
                $graphType = 'bgp_' . $view;
                $graphId = $peer->bgpPeer_id;

                if ($view === 'updates') {
                    $showGraph = true;
                } elseif (str_starts_with($view, 'prefixes_')) {
                    [, $afisafi] = explode('_', $view);
                    if (! empty($afisafiMap[$afisafi])) {
                        $showGraph = true;
                    }
                } elseif ($view === 'macaccounting_bits' || $view === 'macaccounting_pkts') {
                    $macRow = DB::table('ipv4_mac')
                        ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
                        ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
                        ->where('ipv4_mac.ipv4_address', $peer->bgpPeerIdentifier)
                        ->where('ports.device_id', $device->device_id)
                        ->select('mac_accounting.ma_id')
                        ->first();
                    if ($macRow) {
                        $showGraph = true;
                        $graphId = $macRow->ma_id;
                        $graphType = $view;
                    }
                }

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
                    'last_error' => $lastError,
                    'afi_list' => $afiList,
                    'linked_port' => $linkedPort,
                    'peer_type' => $peerType,
                    'peer_type_class' => $peerTypeClass,
                    'show_graph' => $showGraph,
                    'graph_type' => $graphType,
                    'graph_id' => $graphId,
                ];
            });

        return view('device.tabs.routing.bgp', [
            'device' => $device,
            'view' => $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_options' => $bgpOptions,
            'peers' => $peers,
        ]);
    }
}
