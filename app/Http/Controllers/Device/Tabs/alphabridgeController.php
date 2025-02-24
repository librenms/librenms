<?php
/**
 * LatencyController.php
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
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Smokeping;
use App\Models\PortVlan;

class alphabridgeController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->vlans()->exists();
    }

    public function slug(): string
    {
        return 'alphabridge';
    }

    public function icon(): string
    {
        return 'fa fa-audio-description';
    }

    public function name(): string
    {
        return __('Alphabridge');
    }

    public function data(Device $device, Request $request): array
    {
        return [
            'vlans' => self::getVlans($device),
            'submenualphabridge' => [
                'L2 Configuration' => [
                    ['name' => 'Bits', 'url' => 'bits'],
                    ['name' => 'GVRP Configuration', 'url' => 'gvrp_config'],
                    ['name' => 'STP Configuration', 'url' => 'stp_config'],
                    ['name' => 'Basic ARP', 'url' => 'basic_arp'],
                    ['name' => 'VLAN Configuration', 'url' => 'vlan_config'],
                    ['name' => 'IGMP Snooping', 'url' => 'igmp_snooping'],
                    ['name' => 'LLDP Configuration', 'url' => 'lldp_config'],
                    ['name' => 'DDM Configuration', 'url' => 'ddm_config'],
                ],
                'L3 Configuration' => [
                    ['name' => 'VLAN Interfaces and IP Addresses', 'url' => 'vlan-ips'],
                    ['name' => 'DHCP Client Configuration', 'url' => 'dhcp-client'],
                    ['name' => 'DHCP Server Configuration', 'url' => 'dhcp-server'],
                    ['name' => 'Static Routing', 'url' => 'static-routing'],
                    ['name' => 'VLAN Interface IPv6 Configuration', 'url' => 'vlan-ipv6'],
                    ['name' => 'IPv6 DHCP Client Configuration', 'url' => 'ipv6-dhcp-client'],
                    ['name' => 'IPv6 DHCP Server Configuration', 'url' => 'ipv6-dhcp-server'],
                    ['name' => 'IPv6 Route Configuration', 'url' => 'ipv6-route'],
                    ['name' => 'OSPF Route Configuration', 'url' => 'ospf-route'],
                    ['name' => 'IGMP Proxy', 'url' => 'igmp-proxy'],
                ],
            ],
        ];
    }

    private static function getVlans(Device $device)
    {
        // port.device needed to prevent loading device multiple times
        $portVlan = PortVlan::where('ports_vlans.device_id', $device->device_id)
            ->join('vlans', function ($join) {
                $join
                ->on('ports_vlans.vlan', 'vlans.vlan_vlan')
                ->on('vlans.device_id', 'ports_vlans.device_id');
            })
            ->join('ports', function ($join) {
                $join
                ->on('ports_vlans.port_id', 'ports.port_id');
            })
            ->with(['port.device'])
            ->select('ports_vlans.*', 'vlans.vlan_name')->orderBy('vlan_vlan')->orderBy('ports.ifName')->orderBy('ports.ifDescr')
            ->get()->sortBy(['vlan', 'port']);

        $data = $portVlan->groupBy('vlan');

        return $data;
    }
}
