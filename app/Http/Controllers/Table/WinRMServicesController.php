<?php
/**
 * WinRMServicesController.php
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
use App\Models\WinRMServices;

class WinRMServicesController extends TableController
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
            'display_name', 'service_name', 'status', 'service_type',
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
        return WinRMServices::join('devices', 'winrm_services.device_id', '=', 'devices.device_id')
        ->select('winrm_services.id', 'winrm_services.device_id', 'devices.hostname', 'devices.sysName', 'winrm_services.display_name', 'winrm_services.service_name', 'winrm_services.status', 'winrm_services.alerts', 'winrm_services.service_type')
        ->where('winrm_services.disabled', '=', false)
        ->when($request->device_id, function ($query, $device_id) {
            $query->where('winrm_services.device_id', '=', $device_id);
        })
        ->when($request->service_name, function ($query, $service_name) {
            $query->where('winrm_services.display_name', '=', $service_name);
            $query->orWhere('winrm_services.service_name', '=', $service_name);
        });
    }

    /**
     * @param  WinRMServices  $services
     * @return array
     */
    public function formatItem($services)
    {
        return $services;
    }
}
