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
use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class MapController extends Controller
{
    protected static function deviceLinks($request)
    {
        // Return a blank array for unknown link types
        if($request->get('link_type') != "xdp") {
            return [];
        }

        // Device links are not in the schema yet, so we need to do a table query
        $linkQuery = \DB::table('links as l')
            ->select(\DB::raw('ll.id AS left_id'),
                \DB::raw('ll.lat AS left_lat'),
                \DB::raw('ll.lng AS left_lng'),
                \DB::raw('rl.id AS right_id'),
                \DB::raw('rl.lat AS right_lat'),
                \DB::raw('rl.lng AS right_lng'),
                \DB::raw('sum(lp.ifSpeed) AS link_capacity'),
                \DB::raw('sum(lp.ifOutOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_out_usage_pct'),
                \DB::raw('sum(lp.ifInOctets_rate) * 8 / sum(lp.ifSpeed) * 100 as link_in_usage_pct'))
            ->join('devices AS ld', 'l.local_device_id', '=', 'ld.device_id')
            ->join('locations AS ll', 'ld.location_id', '=', 'll.id')
            ->join('ports AS lp', 'l.local_port_id', '=', 'lp.port_id')
            ->join('devices AS rd', 'l.remote_device_id', '=', 'rd.device_id')
            ->join('locations AS rl', 'rd.location_id', '=', 'rl.id')
            ->where('ld.location_id', '<>', 'rd.location_id')
            ->where('ld.disabled', '=', 0)
            ->where('ld.ignore', '=', 0)
            ->where('rd.disabled', '=', 0)
            ->where('rd.ignore', '=', 0)
            ->where('lp.ifType', '=', 'ethernetCsmacd')
            ->where('lp.ifOperStatus', '=', 'up')
            ->where('lp.ifOutOctets_rate', '<>', 0)
            ->where('lp.ifInOctets_rate', '<>', 0)
            ->whereNotNull('ll.lat')
            ->whereNotNull('ll.lng')
            ->whereNotNull('rl.lat')
            ->whereNotNull('rl.lng')
            ->whereIn('ld.status', [0, 1])
            ->whereIn('rd.status', [0, 1])
            ->groupByRaw('left_id, right_id, left_lat, left_lng, right_lat, right_lng');

        if(! \Auth::user()->hasGlobalRead()) {
            $linkQuery->whereIntegerInRaw("l.local_device_id", \Permissions::devicesForUser($request->user()))
                ->whereIntegerInRaw("l.remote_device_id", \Permissions::devicesForUser($request->user()));
        }

        $group_id = $request->get('group');
        if($group_id) {
            $linkQuery->join('device_group_device AS ldg', 'ld.device_id', '=', 'ldg.device_id')
                ->join('device_group_device AS rdg', 'rd.device_id', '=', 'rdg.device_id')
                ->where('rd.device_group_id', '=', $group_id)
                ->where('ld.device_group_id', '=', $group_id);
        }

        return $linkQuery->get();
    }

    protected static function deviceList($request)
    {
        $group_id = $request->get('group');
        $valid_loc = $request->get('location_valid');
        $disabled = $request->get('disabled');
        $ignore = $request->get('ignore');
        $disabled_alerts = $request->get('disabled_alerts');
        $linkType = $request->get('link_type');

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
            if($linkType == "depends") {
                return $deviceQuery->with('parents')->get();
            } else {
                return $deviceQuery->get();
            }
        }

        if($linkType == "depends") {
            $devices = $deviceQuery->with([
                'parents' => function ($query) use ($request) {
                    $query->hasAccess($request->user());
                },
                'children' => function ($query) use ($request) {
                    $query->hasAccess($request->user());
                }, ])
            ->get();

            return $devices->merge($devices->map->only('children', 'parents')->flatten())->loadMissing('parents', 'location');
        } else {
            return $deviceQuery->get();
        }
    }

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

    protected function nodeHighlightStyle()
    {
        return ['color' => [
            'highlight' => [
                'border' => Config::get('network_map_legend.highlight.border'),
            ],
            'border' => Config::get('network_map_legend.highlight.border'),
        ],
            'borderWidth' => Config::get('network_map_legend.highlight.borderWidth'),
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

    protected function deviceStyle($device, $highlight_node = 0)
    {
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

        return $device_style;
    }

    // GET Device
    public function getDevices(Request $request)
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

    // GET Device Links
    public function getLinks(Request $request)
    {
        // List all links
        $link_list = [];
        foreach (self::deviceLinks($request) as $link) {
            $speed = $link->link_capacity / 1000000000;
            if ($speed > 500000) {
                $width = 20;
            } else {
                $width = round(0.77 * pow($speed, 0.25));
            }

            $link_used = max($link->link_out_usage_pct, $link->link_in_usage_pct);
            $link_used = round(2 * $link_used, -1) / 2;
            if ($link_used > 100) {
                $link_used = 100;
            }
            if (is_nan($link_used)) {
                $link_used = 0;
            }
            $link_color = Config::get("network_map_legend.$link_used");

            $link_list[$link->left_id . "." . $link->right_id] = [
                'local_lat'  => $link->left_lat,
                'local_lng'  => $link->left_lng,
                'remote_lat' => $link->right_lat,
                'remote_lng' => $link->right_lng,
                'color'      => $link_color,
                'width'      => $width,
            ];
        }
        return response()->json($link_list);
    }

    // Full Screen Map
    public function fullscreenMap(Request $request)
    {
        $group_name = NULL;
        if($request->get('group')) {
            $group_name = DeviceGroup::where('id', '=', $request->get('group'))->first('name');
            if (! empty($group_name)) {
                $group_name = $group_name->name;
            }
        }

        $data = [
            'map_engine' => Config::get('map.engine', 'leaflet'),
            'map_provider' => Config::get('geoloc.engine', 'openstreetmap'),
            'map_api_key' => Config::get('geoloc.api_key', ''),
            'show_netmap' => Config::get('network_map_show_on_worldmap', false),
            'netmap_source' => Config::get('network_map_worldmap_link_type', 'xdp'),
            'netmap_include_disabled_alerts' => Config::get('network_map_worldmap_show_disabled_alerts', true)?1:0,
            'page_refresh' => Config::get('page_refresh', 300),
            'init_lat' => Config::get('leaflet.default_lat', 51.48),
            'init_lng' => Config::get('leaflet.default_lng', 0),
            'init_zoom' => Config::get('leaflet.default_zoom', 5),
            'group_radius' => Config::get('leaflet.group_radius', 80),
            'tile_url' => Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
            'group_id' => $request->get('group'),
            'group_name' => $group_name,
            'valid_loc' => $request->get('location_valid'),
            'disabled' => $request->get('disabled'),
            'ignore' => $request->get('ignore'),
            'disabled_alerts' => $request->get('disabled_alerts'),
        ];

        return view('map.fullscreen', $data);
    }
}
