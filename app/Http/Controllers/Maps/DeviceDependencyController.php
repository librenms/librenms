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

use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class DeviceDependencyController extends MapController
{
    protected $isolatedDeviceId = -1;

    protected $deviceIdAll = [];

    private $parentDeviceIds = [];

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
        $highlight_node = $request->get('highlight_node');
        $show_device_path = $request->get('showparentdevicepath');

        $dependencies = [];
        $devices_by_id = array();
        $device_list = [];

        // collect Device IDs and Parents/Children to find isolated Devices
        $device_associations = [];

        // For manual level we need to track some items
        $next_level_devices = array();
        $device_child_map = array();
        $processed_devices = array();

        // List all devices
        foreach (self::deviceList($request) as $device) {
            $device_list[] = ['id' => $device->device_id, 'label' => $device->hostname];
            $this->deviceIdAll[] = $device->device_id;

            // List all Device
            $devices_by_id[$device->device_id] = array_merge(
                [
                    'id' => $device->device_id,
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
                    'from' => $device->device_id,
                    'to' => $parent->device_id,
                    'width' => 2,
                ];
                // Keep track of the parent->child relationship
                if(! array_key_exists($parent->device_id, $device_child_map)) {
                    $device_child_map[$parent->device_id] = array();
                }
                $device_child_map[$parent->device_id][$device->device_id] = True;
            }
            if(! count($parents)) {
                // This is a top level device
                $next_level_devices[$device->device_id] = True;
            }
        }

        $this_level=0;
        while (count($next_level_devices)) {
            $this_level_devices = $next_level_devices;
            $next_level_devices = array();

            foreach (array_keys($this_level_devices) as $device_id) {
                // Ignore if the device has already been processed
                if (array_key_exists($device_id, $processed_devices)) {
                    continue;
                }

                // Set device level and mark as processed
                $devices_by_id[$device_id]['level'] = $this_level;
                $processed_devices[$device_id] = True;

                // Add any child devices to be processed next
                if (array_key_exists($device_id, $device_child_map)) {
                    $next_level_devices = $next_level_devices + $device_child_map[$device_id];
                }
            }
            $this_level++;
        }

        // We can collapse devices_by_id to be just values
        $devices_by_id = array_values($devices_by_id);

        // highlight isolated Devices
        if ($highlight_node == $this->isolatedDeviceId) {
            $device_associations = array_unique($device_associations);
            $isolated_device_ids = array_diff($this->deviceIdAll, $device_associations);

            $devices_by_id = $this->highlightDevices($devices_by_id, $isolated_device_ids);
        } elseif ($show_device_path && ($highlight_node > 0)) {
            foreach (self::deviceList($request) as $device) {
                if ($device->device_id != $highlight_node) {
                    continue;
                }
                $this->getParentDevices($device);
                break;
            }
            $devices_by_id = $this->highlightDevices($devices_by_id, $this->parentDeviceIds);
        }

        $device_list_labels = array_column($device_list, 'label');
        array_multisort($device_list_labels, SORT_ASC, $device_list);

        $group_name = DeviceGroup::where('id', '=', $group_id)->first('name');
        if (! empty($group_name)) {
            $group_name = $group_name->name;
        }

        $data = [
            'showparentdevicepath' => $show_device_path,
            'isolated_device_id' => $this->isolatedDeviceId,
            'device_list' => $device_list,
            'group_id' => $group_id,
            'highlight_node' => $highlight_node,
            'node_count' => count($devices_by_id),
            'options' => $this->visOptions(),
            'nodes' => json_encode(array_values($devices_by_id)),
            'edges' => json_encode($dependencies),
            'group_name' => $group_name,
        ];

        return view('map.device-dependency', $data);
    }
}
