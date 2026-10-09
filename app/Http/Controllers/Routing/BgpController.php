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

namespace App\Http\Controllers\Routing;

use App\Http\Controllers\Controller;
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\View\Components\Routing\BgpPeerTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BgpController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'type' => 'nullable|in:all,internal,external',
            'adminstatus' => 'nullable|in:start,stop',
            'state' => 'nullable|in:down',
            'graph' => 'nullable|in:' . implode(',', BgpPeerTable::GRAPHS),
        ]);

        $type = $request->query('type', 'all');
        $adminStatus = $request->query('adminstatus');
        $state = $request->query('state');
        $graph = $request->query('graph');
        $user = $request->user();

        $peers = BgpPeer::hasAccess($user)
            ->select('bgpPeers.*')
            ->join('devices', 'devices.device_id', '=', 'bgpPeers.device_id')
            ->when($type === 'internal', fn ($q) => $q->whereColumn('devices.bgpLocalAs', '=', 'bgpPeers.bgpPeerRemoteAs'))
            ->when($type === 'external', fn ($q) => $q->whereColumn('devices.bgpLocalAs', '!=', 'bgpPeers.bgpPeerRemoteAs'))
            ->when($adminStatus === 'stop', fn ($q) => $q->where('bgpPeerAdminStatus', 'stop'))
            ->when($adminStatus === 'start', fn ($q) => $q->whereIn('bgpPeerAdminStatus', ['start', 'running']))
            ->when($state === 'down', fn ($q) => $q->where('bgpPeerState', '!=', 'established'))
            ->with('device')
            ->orderBy('devices.hostname')
            ->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get();

        $link = fn (array $params) => route('routing.bgp', array_filter(array_merge($request->query(), $params), fn ($v) => $v !== null));

        $activeAfis = BgpPeerCbgp::hasAccess($user)->distinct()->get(['afi', 'safi'])
            ->map(fn (BgpPeerCbgp $c) => $c->afi . $c->safi)->all();

        return view('routing.bgp', [
            'peers' => $peers,
            'graph' => $graph,
            'type' => $type,
            'admin_status' => $adminStatus ?? 'any',
            'state' => $state ?? 'any',
            'type_options' => [
                'all' => ['text' => __('All'), 'link' => $link(['type' => null])],
                'internal' => ['text' => __('iBGP'), 'link' => $link(['type' => 'internal'])],
                'external' => ['text' => __('eBGP'), 'link' => $link(['type' => 'external'])],
            ],
            'admin_options' => [
                'any' => ['text' => __('Any'), 'link' => $link(['adminstatus' => null])],
                'start' => ['text' => __('Enabled'), 'link' => $link(['adminstatus' => 'start'])],
                'stop' => ['text' => __('Shutdown'), 'link' => $link(['adminstatus' => 'stop'])],
            ],
            'state_options' => [
                'any' => ['text' => __('Any'), 'link' => $link(['state' => null])],
                'down' => ['text' => __('Down'), 'link' => $link(['state' => 'down'])],
            ],
            'graph_options' => $this->graphOptions($link, $activeAfis, ! empty(BgpPeerTable::macAccountingIds($peers))),
        ]);
    }

    /**
     * @param  callable(array<string, string|null>): string  $link
     * @param  string[]  $activeAfis
     * @return array<string, array{text: string, link: string}>
     */
    private function graphOptions(callable $link, array $activeAfis, bool $hasMacAccounting): array
    {
        $options = [
            'none' => ['text' => __('None'), 'link' => $link(['graph' => null])],
            'updates' => ['text' => __('Updates'), 'link' => $link(['graph' => 'updates'])],
        ];

        $prefixes = [
            'ipv4unicast' => __('IPv4 Ucast'),
            'ipv4multicast' => __('IPv4 Mcast'),
            'ipv4vpn' => __('VPNv4 Ucast'),
            'ipv6unicast' => __('IPv6 Ucast'),
            'ipv6multicast' => __('IPv6 Mcast'),
            'ipv6vpn' => __('VPNv6 Ucast'),
        ];

        foreach ($prefixes as $afiSafi => $text) {
            if (in_array($afiSafi, $activeAfis, true)) {
                $options["prefixes_$afiSafi"] = ['text' => $text, 'link' => $link(['graph' => "prefixes_$afiSafi"])];
            }
        }

        if ($hasMacAccounting) {
            $options['macaccounting_bits'] = ['text' => __('MAC Bits'), 'link' => $link(['graph' => 'macaccounting_bits'])];
            $options['macaccounting_pkts'] = ['text' => __('MAC Packets'), 'link' => $link(['graph' => 'macaccounting_pkts'])];
        }

        return $options;
    }
}
