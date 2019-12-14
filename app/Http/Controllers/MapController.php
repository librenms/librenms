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
    public function __construct()
    {
        $this->middleware('deny-demo');
    }

    protected function visOptions()
    {
        return Config::get('network_map_vis_options');
    }

    protected function nodeDisabledStyle()
    {
        return array('color' => array(
                         'highlight' => array(
                             'background' => Config::get('network_map_legend.di.node'),
                         ),
                         'border' => Config::get('network_map_legend.di.border'),
                         'background' => Config::get('network_map_legend.di.node'),
                     ),
                 );
    }

    protected function nodeDownStyle()
    {
        return array('color' => array(
                         'highlight' => array(
                             'background' => Config::get('network_map_legend.dn.node'),
                             'border' => Config::get('network_map_legend.dn.border'),
                         ),
                         'border' => Config::get('network_map_legend.dn.border'),
                         'background' => Config::get('network_map_legend.dn.node'),
                     ),
                 );
    }

    protected function nodeUpStyle()
    {
        return array();
    }

    // Device Dependency Map
    public function dependencyMap(Request $request)
    {
        $devices = Device::hasAccess($request->user())->with('parents')->get();

        $dependencies = array();
        $devices_by_id  = [];

        // Build the style variables we need

        // List all devices
        foreach ($devices as $items) {
            if ($items['disabled']) {
                $device_style = $this->nodeDisabledStyle();
            } elseif (! $items['status']) {
                $device_style = $this->nodeDownStyle();
            } else {
                $device_style = $this->nodeUpStyle();
            }

            // List all Device
            $devices_by_id[$items['device_id']] = array_merge(
                array(
                    'id'    => $items['device_id'],
                    'label' => $items->shortDisplayName(),
                    'title' => Url::deviceLink($items, Null, [], 0, 0, 0, 0),
                    'shape' => 'box',
                ),
                $device_style
            );

            // List all Device Dependencies
            $parents = $items->parents()->get();
            foreach ($parents as $parent) {
                $dependencies[] = array(
                                        'from'  => $items['device_id'],
                                        'to'    => $parent['device_id'],
                                        'width' => 2,
                                  );
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
