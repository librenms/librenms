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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Maps;

use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

class DeviceDependencyController extends MapController
{

    protected $isolated_device_id = -1;

    protected static function deviceList($request)
    {
        $group_id = $request->get('group');

        if (! $group_id) {
            return Device::hasAccess($request->user())->with('parents', 'location')->get();
        }

        $devices = Device::inDeviceGroup($group_id)
            ->hasAccess($request->user())
            ->with([
                'location',
                'parents' => function ($query) use ($request) {
                    $query->hasAccess($request->user());
                },
                'children' => function ($query) use ($request) {
                    $query->hasAccess($request->user());
                }])
            ->get();
        return $devices->merge($devices->map->only('children', 'parents')->flatten())->loadMissing('parents', 'location');
    }

    protected function highlightIsolatedDevices($devices_by_id, $isolated_device_ids)
    {
        $new_device_list = [];
        foreach ($devices_by_id as $device) {
            if (in_array($device['id'], $isolated_device_ids)) {
                $new_device_list[] = array_merge($device, $this->nodeHighlightStyle());
                continue;
            }
            $new_device_list[] = $device;
        }

        return $new_device_list;
    }

    // Device Dependency Map
    public function dependencyMap(Request $request)
    {
        $group_id = $request->get('group');
        $highlight_node = $request->get('highlight_node');

        $dependencies = [];
        $devices_by_id = [];
        $device_list = [];

        // collect Device IDs and Parents/Children to find isolated Devices
        $device_id_all = [];
        $device_associations = [];

        // List all devices
        foreach (self::deviceList($request) as $device) {
            $device_list[] = ['id' => $device->device_id, 'label' => $device->hostname];
            $device_id_all[] = $device->device_id;

            // List all Device
            $devices_by_id[] = array_merge(
                [
                    'id'    => $device->device_id,
                    'label' => $device->shortDisplayName(),
                    'title' => Url::deviceLink($device, null, [], 0, 0, 0, 0),
                    'shape' => 'box',
                ],
                $this->deviceStyle($device, $highlight_node)
            );

            // List all Device Dependencies
            $children = $device->children;
            foreach ($children as $child) {
                $device_associations[] = $child->device_id;
            }

            $parents = $device->parents;
            foreach ($parents as $parent) {
                $device_associations[] = $parent->device_id;
                $dependencies[] = [
                    'from'  => $device->device_id,
                    'to'    => $parent->device_id,
                    'width' => 2,
                ];
            };
        }

        // highlight isolated Devices
        if ($highlight_node == $this->isolated_device_id) {
            $device_associations = array_unique($device_associations);
            $isolated_device_ids = array_diff($device_id_all, $device_associations);

            $devices_by_id = $this->highlightIsolatedDevices($devices_by_id, $isolated_device_ids);
        }

        array_multisort(array_column($device_list, 'label'), SORT_ASC, $device_list);

        $data = [
            'isolated_device_id' => $this->isolated_device_id,
            'device_list' => $device_list,
            'group_id' => $group_id,
            'highlight_node' => $highlight_node,
            'node_count' => count($devices_by_id),
            'options' => $this->visOptions(),
            'nodes' => json_encode(array_values($devices_by_id)),
            'edges' => json_encode($dependencies),
        ];

        return view('map.device-dependency', $data);
    }
}
