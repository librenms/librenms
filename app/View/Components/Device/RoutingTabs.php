<?php

/**
 * RoutingTabs.php
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

namespace App\View\Components\Device;

use App\Models\Component;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component as BladeComponent;

class RoutingTabs extends BladeComponent
{
    /**
     * @var array<string, array{text: string, link: string}>
     */
    public array $tabs = [];

    public function __construct(
        public readonly Device $device,
        public readonly ?string $tab = null,
    ) {
        $routingTabs = self::getRoutingTabs($this->device);
        $tabLabels = self::getTabLabels();

        foreach ($routingTabs as $type => $typeCount) {
            $routeName = 'device.routing.' . $type;
            $this->tabs[$type] = [
                'text' => ($tabLabels[$type] ?? ucfirst($type)) . ' (' . $typeCount . ')',
                'link' => Route::has($routeName) ? route($routeName, $this->device) : '#',
            ];
        }
    }

    /**
     * @return array<string, int>
     */
    public static function getRoutingTabs(Device $device): array
    {
        if (Gate::none(['routing.view', 'routing.viewAll'])) {
            return [];
        }

        return array_filter([
            'bgp' => $device->bgppeers()->count(),
            'ospf' => $device->ospfInstances()->count(),
            'ospfv3' => $device->ospfv3Instances()->count(),
            'isis' => $device->isisAdjacencies()->count(),
            'vrf' => $device->vrfs()->count(),
            'cef' => $device->cefSwitching()->count(),
            'mpls' => $device->mplsServices()->count(),
            'cisco-otv' => Component::query()->where('device_id', $device->device_id)->where('type', 'Cisco-OTV')->count(),
            'ipsec-tunnels' => $device->ipsecTunnels()->count(),
            'routes' => $device->routes()->count(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getTabLabels(): array
    {
        return [
            'ipsec-tunnels' => __('IPSEC Tunnels'),
            'bgp' => __('BGP'),
            'cef' => __('CEF'),
            'ospf' => __('OSPF'),
            'ospfv3' => __('OSPFv3'),
            'isis' => __('ISIS'),
            'vrf' => __('VRFs'),
            'routes' => __('Routing Table'),
            'cisco-otv' => __('OTV'),
            'mpls' => __('MPLS'),
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.device.routing-tabs');
    }
}
