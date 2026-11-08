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
use App\Models\Device;
use App\View\Components\Routing\BgpPeerTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BgpController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $request->validate([
            'view' => 'nullable|in:basic,' . implode(',', BgpPeerTable::GRAPHS),
        ]);

        $view = $request->query('view', 'basic');

        $peers = $device->bgppeers()
            ->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get();
        $peers->each->setRelation('device', $device);

        $activeAfis = $device->bgpPeersCbgp()->distinct()->get(['afi', 'safi'])
            ->map(fn ($c) => $c->afi . $c->safi)->all();

        return view('device.tabs.routing.bgp', [
            'device' => $device,
            'view' => $view,
            'graph' => $view === 'basic' ? null : $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_menu' => $this->buildMenu($device, $activeAfis, ! empty(BgpPeerTable::macAccountingIds($peers))),
            'peers' => $peers,
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
            'ipv4multicast' => __('IPv4 Mcast'),
            'ipv4vpn' => __('VPNv4 Ucast'),
            'ipv6unicast' => __('IPv6 Ucast'),
            'ipv6multicast' => __('IPv6 Mcast'),
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
}
