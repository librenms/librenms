<?php
/**
 * AvailabilityMapController.php
 *
 * Controller for availability maps
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Config;

class AvailabilityMapController extends Controller
{
    // Availability Map
    public function availabilityMap(Request $request): View
    {
        $data = [
            'page_refresh' => Config::get('page_refresh', 300),
            'compact' => Config::get('webui.availability_map_compact'),
            'box_size' => Config::get('webui.availability_map_box_size'),
            'sort' => Config::get('webui.availability_map_sort_status') ? 'status' : 'hostname',
            'use_groups' => Config::get('webui.availability_map_use_device_groups'),
            'services' => Config::get('show_services'),
            'uptime_warn' => Config::get('uptime_warning'),
            'devicegroups' => Config::get('webui.availability_map_use_device_groups') ? DeviceGroup::hasAccess($request->user())->orderBy('name')->get(['id', 'name']) : [],
        ];

        return view('map.availability', $data);
    }
}
