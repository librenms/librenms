<?php
/**
 * WinRMController.php
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
use App\Models\WinRMProcesses;
use App\Models\WinRMServices;

class WinRMController extends TableController
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
        switch ($request->page_id) {
            case 'processes':
                return [
                    'name', 'username', 'ws', 'vm', 'cpu',
                ];
                break;
            case 'services':
                return [
                    'display_name', 'service_name', 'status', 'service_type',
                ];
                break;
            case 'software':
                return [
                    'name', 'vendor', 'description', 'version',
                ];
                break;
        }
        // return ['winrm_software.name', 'winrm_software.vendor', 'winrm_software.description', 'winrm_device_software.version'];
    }

    protected function sortFields($request)
    {
        switch ($request->page_id) {
            case 'processes':
                return [
                    'name', 'username', 'ws', 'vm', 'cpu',
                ];
                break;
            case 'services':
                return [
                    'display_name', 'service_name', 'status', 'service_type',
                ];
                break;
            case 'software':
                return [
                    'name', 'vendor', 'description', 'version',
                ];
                break;
        }
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        switch ($request->page_id) {
            case 'processes':
                return  WinRMProcesses::join('devices', 'winrm_processes.device_id', '=', 'devices.device_id')
                ->select('winrm_processes.device_id', 'devices.hostname', 'devices.sysName', 'winrm_processes.name', 'winrm_processes.username', 'winrm_processes.ws', 'winrm_processes.vm', 'winrm_processes.cpu')
                ->when($request->device_id, function ($query, $device_id) {
                    $query->where('winrm_processes.device_id', '=', $device_id);
                })
                ->when($request->process_name, function ($query, $process_name) {
                    $query->where('winrm_processes.name', '=', $process_name);
                });
                break;
            case 'services':
                return  WinRMServices::join('devices', 'winrm_services.device_id', '=', 'devices.device_id')
                ->select('winrm_services.id', 'winrm_services.device_id', 'devices.hostname', 'devices.sysName', 'winrm_services.display_name', 'winrm_services.service_name', 'winrm_services.status', 'winrm_services.alerts', 'winrm_services.service_type')
                ->where('winrm_services.disabled', '=', false)
                ->when($request->device_id, function ($query, $device_id) {
                    $query->where('winrm_services.device_id', '=', $device_id);
                })
                ->when($request->service_name, function ($query, $service_name) {
                    $query->where('winrm_services.display_name', '=', $service_name);
                    $query->orWhere('winrm_services.service_name', '=', $service_name);
                });
                break;
            case 'software':
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
                break;
        }

        // return WinRMDeviceSoftware::hasAccess($request->user())
        //     ->with('winrm_device_software')
        //     ->join('winrm_software', 'winrm_device_software.software_id', '=', 'winrm_software.id')
        //     ->select('name', 'vendor', 'description', 'version')
        //     ->when($request->device_id, function ($query, $device_id) {
        //         $query->where('device_id', '=', $device_id);
        //     });
    }

    /**
     * @param  winrsoftware  $winrsoftware
     */
    public function formatItem($model)
    {
        return $model;
    }
}
