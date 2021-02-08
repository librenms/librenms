<?php
/**
 * VlansController.php
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

use App\Models\Device;
use App\Models\PortVlan;
use LibreNMS\Interfaces\UI\DeviceTab;

class VlansController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->vlans()->exists();
    }

    public function slug(): string
    {
        return 'vlans';
    }

    public function icon(): string
    {
        return 'fa-tasks';
    }

    public function name(): string
    {
        return __('VLANs');
    }

    public function data(Device $device): array
    {
        return [
            'vlans' => self::getVlans($device),
            'submenu' => [
                [
                    ['name' => 'Basic', 'url' => ''],
                ],
                'Graphs' => [
                    ['name' => 'Bits', 'url' => 'bits'],
                    ['name' => 'Unicast Packets', 'url' => 'upkts'],
                    ['name' => 'Non-Unicast Packets', 'url' => 'nupkts'],
                    ['name' => 'Errors', 'url' => 'errors'],
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
            ->with(['port.device'])
            ->select('ports_vlans.*', 'vlans.vlan_name')
            ->get();

        $data = $portVlan->groupBy('vlan');

        return $data;
    }
}
