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
 * @author     Thomas Ford<tford@thomasaford.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use App\Models\WinRMSoftware;
use Carbon\Carbon;
use DB;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use Request;

class WinRMController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        $show_services = $device->winrmservices()->exists();
        $show_software = $device->winrmdevicesoftware()->exists();
        $show_processes = $device->winrmprocesses()->exists();
        // Need to look at tables and see if we should show this. 
        if($show_services || $show_software  || $show_processes){
            return true;
        }
        return false;
    }

    public function slug(): string
    {
        return 'winrm';
    }

    public function icon(): string
    {
        return 'fa fa-windows';
    }

    public function name(): string
    {
        return __('WinRM');
    }
    
    public function data(Device $device): array
    {
        return [];
    }

    public function subtabdata(Device $device, string $pageVars): array
    {
        return [
            'show_services' => $device->winrmservices()->exists(),
            'show_software' => $device->winrmdevicesoftware()->exists(),
            'show_processes' => $device->winrmprocesses()->exists(),
            'page_id' => (empty($pageVars) ? 'software' : $pageVars),
            'device_id' => $device->device_id,
        ];
    }
}


