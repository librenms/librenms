<?php
/**
 * FullscreenMapController.php
 *
 * Controller for full screen map
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use LibreNMS\Config;

class FullscreenMapController extends Controller
{
    protected function fullscreenMap(Request $request): View|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'group' => 'int',
            'lat' => 'numeric',
            'lng' => 'numeric',
            'zoom' => 'numeric',
        ]);

        if ($validator->fails()) {
            return redirect('fullscreenmap');
        }

        $group_name = null;
        if ($request->get('group')) {
            $group_name = DeviceGroup::where('id', '=', $request->get('group'))->first('name');
            if (! empty($group_name)) {
                $group_name = $group_name->name;
            }
        }

        $init_lat = $request->get('lat');
        if (! $init_lat) {
            $init_lat = Config::get('leaflet.default_lat', 51.48);
        }

        $init_lng = $request->get('lng');
        if (! $init_lng) {
            $init_lng = Config::get('leaflet.default_lng', 0);
        }

        $init_zoom = $request->get('zoom');
        if (! $init_zoom) {
            $init_zoom = Config::get('leaflet.default_zoom', 5);
        }

        $data = [
            'map_engine' => Config::get('map.engine', 'leaflet'),
            'map_provider' => Config::get('geoloc.engine', 'openstreetmap'),
            'map_api_key' => Config::get('geoloc.api_key', ''),
            'show_netmap' => Config::get('network_map_show_on_worldmap', false),
            'netmap_source' => Config::get('network_map_worldmap_link_type', 'xdp'),
            'netmap_include_disabled_alerts' => Config::get('network_map_worldmap_show_disabled_alerts', true) ? 'null' : 0,
            'page_refresh' => Config::get('page_refresh', 300),
            'init_lat' => $init_lat,
            'init_lng' => $init_lng,
            'init_zoom' => $init_zoom,
            'group_radius' => Config::get('leaflet.group_radius', 80),
            'tile_url' => Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
            'group_id' => $request->get('group'),
            'group_name' => $group_name,
        ];

        return view('map.fullscreen', $data);
    }
}
