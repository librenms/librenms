<?php
/**
 * WinRMSoftwareController.php
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
 * @copyright  2021 Thomas Ford
 * @author     Thomas Ford <tford@thomasaford.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\WinRMDeviceSoftware;

class WinRMSoftwareController extends TableController
{
    /**
     * Defines search fields will be searched in order
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function searchFields($request)
    {
        return [
            'name', 'vendor', 'description', 'version',
        ];
    }

    protected function sortFields($request)
    {
        return [
            'name', 'vendor', 'description', 'version',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return  WinRMDeviceSoftware::join('winrm_software', 'winrm_device_software.software_id', '=', 'winrm_software.id')
        ->join('devices', 'winrm_device_software.device_id', '=', 'devices.device_id')
        ->select('winrm_device_software.device_id', 'devices.hostname', 'devices.sysName', 'winrm_device_software.software_id', 'winrm_software.name', 'winrm_software.vendor', 'winrm_software.description', 'winrm_device_software.version')
        ->when($request->device_id, function ($query, $device_id) {
            $query->where('winrm_device_software.device_id', '=', $device_id);
        })
        ->when($request->software_id, function ($query, $software_id) {
            $query->where('winrm_device_software.software_id', '=', $software_id);
        })
        ->when($request->software_version, function ($query, $software_version) {
            $query->where('winrm_device_software.version', '=', $software_version);
        })
        ->when($request->software_vendor, function ($query, $software_vendor) {
            $query->where('winrm_software.vendor', '=', $software_vendor);
        });
    }

    /**
     * @param  WinRMDeviceSoftware $software
     * @return array
     */
    public function formatItem($software)
    {
        return $software;
    }
}
