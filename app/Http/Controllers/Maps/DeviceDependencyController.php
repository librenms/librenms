<?php
/**
 * DependencyController.php
 *
 * Controller for graphing Relationships
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
 * @copyright  2019 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use LibreNMS\Config;

class DeviceDependencyController extends Controller
{
    protected $isolatedDeviceId = -1;

    protected $deviceIdAll = [];

    private $parentDeviceIds = [];

    protected static function deviceList($request)
    {
        $group_id = $request->get('group');
        $valid_loc = $request->get('location_valid');
        $disabled = $request->get('disabled');
        $ignore = $request->get('ignore');
        $disabled_alerts = $request->get('disabled_alerts');

        $deviceQuery = Device::hasAccess($request->user())->with('location');

        if ($group_id) {
            $deviceQuery->inDeviceGroup($group_id);
        }

        if (! is_null($disabled)) {
            if ($disabled) {
                $deviceQuery->where('disabled', '<>', '0');
            } else {
                $deviceQuery->where('disabled', '=', '0');
            }
        }

        if (! is_null($ignore)) {
            if ($ignore) {
                $deviceQuery->where('ignore', '<>', '0');
            } else {
                $deviceQuery->where('ignore', '=', '0');
            }
        }

        if (! is_null($disabled_alerts)) {
            if ($disabled_alerts) {
                $deviceQuery->where('disable_notify', '<>', '0');
            } else {
                $deviceQuery->where('disable_notify', '=', '0');
            }
        }

        if ($valid_loc) {
            $deviceQuery->whereHas('location', function ($query) {
                $query->whereNotNull('lng')
                    ->whereNotNull('lat')
                    ->where('lng', '<>', '')
                    ->where('lat', '<>', '');
            });
        }

        if (! $group_id) {
            return $deviceQuery->with('parents')->get();
        }

        $devices = $deviceQuery->with([
            'parents' => function ($query) use ($request) {
                $query->hasAccess($request->user());
            },
            'children' => function ($query) use ($request) {
                $query->hasAccess($request->user());
            }, ])
        ->get();

        return $devices->merge($devices->map->only('children', 'parents')->flatten())->loadMissing('parents', 'location');
    }

    protected function highlightDevices($devices_by_id, $device_id_list)
    {
        $new_device_list = [];
        foreach ($devices_by_id as $device) {
            if (in_array($device['id'], $device_id_list)) {
                $new_device_list[] = array_merge($device, $this->nodeHighlightStyle());
                continue;
            }
            $new_device_list[] = $device;
        }

        return $new_device_list;
    }

    protected function getParentDevices($device)
    {
        foreach ($device->parents as $parent) {
            if (! in_array($parent->device_id, $this->deviceIdAll)) {
                continue;
            }
            if (in_array($parent->device_id, $this->parentDeviceIds)) {
                continue;
            }
            $this->parentDeviceIds[] = $parent->device_id;
            if ($parent) {
                $this->getParentDevices($parent);
            }
        }
    }

    // Device Dependency Map
    public function dependencyMap(Request $request)
    {
        $group_id = $request->get('group');

        $group_name = DeviceGroup::where('id', '=', $group_id)->first('name');
        if (! empty($group_name)) {
            $group_name = $group_name->name;
        }

        $data = [
            'page_refresh' => Config::get('page_refresh', 300),
            'group_id' => $group_id,
            'options' => Config::get('network_map_vis_options'),
            'group_name' => $group_name,
        ];

        return view('map.device-dependency', $data);
    }

    // Device Dependency JSON
    public function dependencyJSON(Request $request)
    {
        // List all devices
        $device_list = [];
        foreach (self::deviceList($request) as $device) {
            $device_list[$device->device_id] = [
                'id'      => $device->device_id,
                'icon'    => $device->icon,
                'sname'   => $device->shortDisplayName(),
                'status'  => $device->status,
                'url'     => Url::deviceUrl($device->device_id),
                'lat'     => $device->location->lat,
                'lng'     => $device->location->lng,
                'parents' => $device->parents->map->only('device_id')->flatten(),
            ];
        }

        return response()->json($device_list);
    }
}
