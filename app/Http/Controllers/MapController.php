<?php
/**
 * MapController.php
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

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class MapController extends Controller
{
    protected function visOptions()
    {
        return Config::get('network_map_vis_options');
    }

    protected function nodeDisabledStyle()
    {
        return ['color' => [
                         'highlight' => [
                             'background' => Config::get('network_map_legend.di.node'),
                         ],
                         'border' => Config::get('network_map_legend.di.border'),
                         'background' => Config::get('network_map_legend.di.node'),
                     ],
               ];
    }

    protected function nodeDownStyle()
    {
        return ['color' => [
                         'highlight' => [
                             'background' => Config::get('network_map_legend.dn.node'),
                             'border' => Config::get('network_map_legend.dn.border'),
                         ],
                         'border' => Config::get('network_map_legend.dn.border'),
                         'background' => Config::get('network_map_legend.dn.node'),
                     ],
               ];
    }

    protected function nodeUpStyle()
    {
        return [];
    }

    // Device Dependency Map
    public function dependencyMap(Request $request)
    {
        $devices = Device::hasAccess($request->user())->with('parents')->get();

        $dependencies = [];
        $devices_by_id  = [];

        // Build the style variables we need

        // List all devices
        foreach ($devices as $device) {
            if ($device['disabled']) {
                $device_style = $this->nodeDisabledStyle();
            } elseif (! $device['status']) {
                $device_style = $this->nodeDownStyle();
            } else {
                $device_style = $this->nodeUpStyle();
            }

            // List all Device
            $devices_by_id[$device['device_id']] = array_merge(
                [
                    'id'    => $device['device_id'],
                    'label' => $device->shortDisplayName(),
                    'title' => Url::deviceLink($device, null, [], 0, 0, 0, 0),
                    'shape' => 'box',
                ],
                $device_style
            );

            // List all Device Dependencies
            $parents = $device->parents()->get();
            foreach ($parents as $parent) {
                $dependencies[] = [
                    'from'  => $device['device_id'],
                    'to'    => $parent['device_id'],
                    'width' => 2,
                ];
            };
        }

        $data = [
            'node_count' => count($devices_by_id),
            'options' => $this->visOptions(),
            'nodes' => json_encode(array_values($devices_by_id)),
            'edges' => json_encode($dependencies),
        ];

        return view('map.dependency', $data);
    }
}
