<?php
/**
 * StpController.php
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
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class StpController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->stpInstances()->exists();
    }

    public function slug(): string
    {
        return 'stp';
    }

    public function icon(): string
    {
        return 'fa-sitemap';
    }

    public function name(): string
    {
        return __('STP');
    }

    public function data(Device $device, Request $request): array
    {
        $active_vlan = Url::parseOptions('vlan', 1);
        $stpInstances = $device->stpInstances;
        $vlanOptions = $stpInstances->pluck('vlan')->mapWithKeys(function ($vlan) use ($device) {
            if (empty($vlan)) {
                $vlan = 1;
            }

            return [$vlan => [
                'text' => $vlan,
                'link' => Url::deviceUrl($device, ['tab' => 'stp', 'vlan' => $vlan]),
            ]];
        });

        return [
            'vlans' => $vlanOptions->all(),
            'vlan' => $active_vlan,
            'device_id' => $device->device_id,
            'stpInstances' => $stpInstances->filter(function ($instance) use ($active_vlan) {
                return $active_vlan == 1 && $instance->vlan == null || $instance->vlan == $active_vlan;
            }),
            'stpPorts' => $device->stpPorts()->where('vlan', $active_vlan)->when($active_vlan == 1, function ($query) {
                return $query->orWhereNull('vlan');
            })->exists(),
            'bootgridUrl' => url('/ajax/table/'),
        ];
    }
}
