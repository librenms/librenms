<?php
/**
 * RoutingController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\DeviceCache;
use App\Models\Component;
use App\Models\Device;
use LibreNMS\Interfaces\UI\DeviceTab;

class RoutingController implements DeviceTab
{
    private $tabs;

    public function __construct()
    {
        $device = DeviceCache::getPrimary();
        //dd($device);
        $this->tabs = [
            'ospf' => $device->ospfInstances()->count(),
            'isis' => $device->isisAdjacencies()->count(),
            'bgp' => $device->bgppeers()->count(),
            'vrf' => $device->vrfs()->count(),
            'cef' => $device->cefSwitching()->count(),
            'mpls' => $device->mplsLsps()->count(),
            'cisco-otv' => Component::query()->where('device_id', $device->device_id)->where('type', 'Cisco-OTV')->count(),
            'loadbalancer_rservers' => $device->rServers()->count(),
            'ipsec_tunnels' => $device->ipsecTunnels()->count(),
            'routes' => $device->routes()->count(),
        ];
    }

    public function visible(Device $device): bool
    {
        return in_array(true, $this->tabs);
    }

    public function slug(): string
    {
        return 'routing';
    }

    public function icon(): string
    {
        return 'fa-random';
    }

    public function name(): string
    {
        return __('Routing');
    }

    public function data(Device $device): array
    {
        return [
            'routing_tabs' => array_filter($this->tabs),
        ];
    }
}
