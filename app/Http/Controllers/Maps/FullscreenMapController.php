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

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class FullscreenMapController extends Controller
{
    protected function fullscreenMap(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Device::class);

        $validator = Validator::make($request->all(), [
            'group' => 'int',
            'lat' => 'numeric',
            'lng' => 'numeric',
            'zoom' => 'numeric',
        ]);

        if ($validator->fails()) {
            return redirect('fullscreenmap');
        }

        $group_id = $request->integer('group');
        $deviceGroup = $group_id ? DeviceGroup::hasAccess($request->user())
            ->select(['id', 'name'])
            ->firstWhere('id', $group_id) : null;

        $init_lat = $request->input('lat');
        if (! $init_lat) {
            $init_lat = LibrenmsConfig::get('leaflet.default_lat', 51.48);
        }

        $init_lng = $request->input('lng');
        if (! $init_lng) {
            $init_lng = LibrenmsConfig::get('leaflet.default_lng', 0);
        }

        $init_zoom = $request->input('zoom');
        if (! $init_zoom) {
            $init_zoom = LibrenmsConfig::get('leaflet.default_zoom', 5);
        }

        $data = [
            'map_engine' => LibrenmsConfig::get('map.engine', 'leaflet'),
            'map_provider' => LibrenmsConfig::get('geoloc.engine', 'openstreetmap'),
            'map_api_key' => LibrenmsConfig::get('geoloc.api_key', ''),
            'show_netmap' => LibrenmsConfig::get('network_map_show_on_worldmap', false),
            'netmap_source' => LibrenmsConfig::get('network_map_worldmap_link_type', 'xdp'),
            'netmap_include_disabled_alerts' => LibrenmsConfig::get('network_map_worldmap_show_disabled_alerts', true) ? 'null' : 0,
            'page_refresh' => LibrenmsConfig::get('page_refresh', 300),
            'init_lat' => $init_lat,
            'init_lng' => $init_lng,
            'init_zoom' => $init_zoom,
            'group_radius' => LibrenmsConfig::get('leaflet.group_radius', 80),
            'tile_url' => LibrenmsConfig::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
            'group_id' => $deviceGroup?->id,
            'group_name' => $deviceGroup?->name,
        ];

        return view('map.fullscreen', $data);
    }
}
