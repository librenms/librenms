<?php
/**
 * WinRMProcessesController.php
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
use App\Models\WinRMProcesses;

class WinRMProcessesController extends TableController
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
            'name', 'username', 'ws', 'vm', 'cpu',
        ];
    }

    protected function sortFields($request)
    {
        return [
            'name', 'username', 'ws', 'vm', 'cpu',
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
        return WinRMProcesses::join('devices', 'winrm_processes.device_id', '=', 'devices.device_id')
        ->select('winrm_processes.device_id', 'devices.hostname', 'devices.sysName', 'winrm_processes.name', 'winrm_processes.username', 'winrm_processes.ws', 'winrm_processes.vm', 'winrm_processes.cpu')
        ->when($request->device_id, function ($query, $device_id) {
            $query->where('winrm_processes.device_id', '=', $device_id);
        })
        ->when($request->process_name, function ($query, $process_name) {
            $query->where('winrm_processes.name', '=', $process_name);
        });
    }

    /**
     * @param  WinRMProcesses  $processes
     * @return array
     */
    public function formatItem($processes)
    {
        return $processes;
    }
}
