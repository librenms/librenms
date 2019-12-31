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
    // Device Dependency Map
    public function dependencyMap(Request $request, $group_id = 0, $highlight_node = 0)
    {
        $devices = Device::hasAccess($request->user())->with('parents', 'location')->get();

        $dependencies = [];
        $devices_by_id  = [];

        // Device IDs of Devices to show
        $device_nodes = [];
        $device_parents = [];
        $device_childs = [];

        // Build the style variables we need

        //get child and parent Device ID's - for showing showing them
        //even if they are not member oder Device Group
        foreach ($devices as $device) {
            if ($group_id) {
                if (! in_array($group_id, $device->groups()->pluck('id')->toArray())) {
                    continue;
                }
            }

            $device_nodes[] = $device->device_id;

            // no Device Group Filter set, no parent/child discovery needed
            if (! $group_id) {
                continue;
            }

            $parents = $device->parents;
            foreach ($parents as $parent) {
                    $device_parents[] = $parent->device_id;
            };

            $childs = $device->children;
            foreach ($childs as $child) {
                    $device_childs[] = $child->device_id;
            };
        }

        $devices_to_show = array_merge($device_nodes, array_merge($device_parents, $device_childs));

        $device_list = [];

        // List all devices
        foreach ($devices as $device) {
            if (! in_array($device->device_id, $devices_to_show)) {
                continue;
            }

            $device_list[] = ['id' => $device->device_id, 'label' => $device->hostname];

            if ($device->disabled) {
                $device_style = $this->nodeDisabledStyle();
            } elseif (! $device->status) {
                $device_style = $this->nodeDownStyle();
            } else {
                $device_style = $this->nodeUpStyle();
            }

            if ($device->device_id == $highlight_node) {
                $device_style = array_merge($device_style, $this->nodeHighlightStyle());
            }

            // List all Device
            $devices_by_id[] = array_merge(
                [
                    'id'    => $device->device_id,
                    'label' => $device->shortDisplayName(),
                    'title' => Url::deviceLink($device, null, [], 0, 0, 0, 0),
                    'shape' => 'box',
                ],
                $device_style
            );

            // List all Device Dependencies
            $parents = $device->parents;
            foreach ($parents as $parent) {
                $dependencies[] = [
                    'from'  => $device->device_id,
                    'to'    => $parent->device_id,
                    'width' => 2,
                ];
            };
        }

        array_multisort(array_column($device_list, 'label'), SORT_ASC, $device_list);

        $data = [
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
