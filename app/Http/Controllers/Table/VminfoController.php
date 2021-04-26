<?php
/**
 * SyslogController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Vminfo;
use LibreNMS\Util\Url;

class VminfoController extends TableController
{
    public function searchFields($request)
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'devices.hostname', 'devices.sysname'];
    }

    public function sortFields($request)
    {
        return ['vmwVmDisplayName', 'vmwVmGuestOS', 'vmwVmMemSize', 'vmwVmCpus', 'vmwVmState', 'hostname'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return Vminfo::hasAccess($request->user())
            ->select('vminfo.*')
            ->with('device')
            ->with('parentDevice')
            ->when($request->get('searchPhrase') || in_array('hostname', array_keys($request->get('sort', []))), function ($query) {
                $query->leftJoin('devices', 'devices.device_id', 'vminfo.device_id');
            });
    }

    /**
     * @param Vminfo $vm
     */
    public function formatItem($vm)
    {
        return [
            'vmwVmState' => '<span class="label ' . $vm->stateLabel[1] . '">' . $vm->stateLabel[0] . '</span>',
            'vmwVmDisplayName' => is_null($vm->parentDevice) ? $vm->vmwVmDisplayName : self::getHostname($vm->parentDevice),
            'vmwVmGuestOS' => $vm->operatingSystem,
            'vmwVmMemSize' => $vm->memoryFormatted,
            'vmwVmCpus' => $vm->vmwVmCpus,
            'hostname' => self::getHostname($vm->device),
            'deviceid' => $vm->device_id,
            'sysname' => $vm->device->sysName,

        ];
    }

    private static function getHostname(Device $device): string
    {
        return '<a class="list-device" href="' . Url::deviceUrl($device) . '">' . $device->hostname . '</a><br>' . $device->sysName;
    }
}
