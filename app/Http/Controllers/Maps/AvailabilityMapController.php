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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityMapController extends Controller
{
    // Availability Map
    public function availabilityMap(Request $request): View
    {
        $data = [
            'page_refresh' => LibrenmsConfig::get('page_refresh', 300),
            'compact' => LibrenmsConfig::get('webui.availability_map_compact'),
            'box_size' => LibrenmsConfig::get('webui.availability_map_box_size'),
            'sort' => LibrenmsConfig::get('webui.availability_map_sort_status') ? 'status' : 'hostname',
            'use_groups' => LibrenmsConfig::get('webui.availability_map_use_device_groups'),
            'services' => LibrenmsConfig::get('show_services'),
            'uptime_warn' => LibrenmsConfig::get('uptime_warning'),
            'devicegroups' => LibrenmsConfig::get('webui.availability_map_use_device_groups') ? DeviceGroup::hasAccess($request->user())->orderBy('name')->get(['id', 'name']) : [],
        ];

        return view('map.availability', $data);
    }
}
