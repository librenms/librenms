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
use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Service;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class MapController extends Controller
{
    protected static function deviceLinks($request)
    {
        // Return a blank array for unknown link types
        if ($request->get('link_type') != 'xdp') {
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

        if (! \Auth::user()->hasGlobalRead()) {
            $linkQuery->whereIntegerInRaw('l.local_device_id', \Permissions::devicesForUser($request->user()))
                ->whereIntegerInRaw('l.remote_device_id', \Permissions::devicesForUser($request->user()));
        }

        $group_id = $request->get('group');
        if ($group_id) {
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
        $statuses = $request->get('statuses');

        $deviceQuery = Device::hasAccess($request->user())->with('location');

        if ($group_id) {
            $deviceQuery->inDeviceGroup($group_id);
        }

        if ($statuses && count($statuses) > 0) {
            $deviceQuery->whereIn('status', $statuses);
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
            if ($linkType == 'depends') {
                return $deviceQuery->with('parents')->get();
            } else {
                return $deviceQuery->get();
            }
        }

        if ($linkType == 'depends') {
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
        return [
            'color' => [
                'highlight' => [
                    'background' => Config::get('network_map_legend.di.node'),
                ],
                'border' => Config::get('network_map_legend.di.border'),
                'background' => Config::get('network_map_legend.di.node'),
            ],
            'borderWidth' => null,
        ];
    }

    protected function nodeHighlightStyle()
    {
        return [
            'color' => [
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
        return [
            'color' => [
                'highlight' => [
                    'background' => Config::get('network_map_legend.dn.node'),
                    'border' => Config::get('network_map_legend.dn.border'),
                ],
                'border' => Config::get('network_map_legend.dn.border'),
                'background' => Config::get('network_map_legend.dn.node'),
            ],
            'borderWidth' => null,
        ];
    }

    protected function nodeUpStyle()
    {
        return [
            'color' => null,
            'border' => null,
            'background' => null,
            'borderWidth' => null,
        ];
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
        // Get all devices under maintenance
        $maintdevices = AlertSchedule::isActive()
            ->with('devices', 'locations.devices', 'deviceGroups.devices')
            ->get()
            ->map->only('devices', 'locations.devices', 'deviceGroups.devices')
            ->flatten();

        // Create a hash of device IDs covered by maintenance to avoid a DB call per device below
        $maintdevicesmap = [];
        foreach ($maintdevices as $device) {
            if ($device) {
                $maintdevicesmap[$device->device_id] = true;
            }
        }

        // For manual level we need to track some items
        $next_level_devices = [];
        $device_child_map = [];
        $processed_devices = [];

        // List all devices
        $device_list = [];
        foreach (self::deviceList($request) as $device) {
            if ($device->status) {
                $updowntime = \LibreNMS\Util\Time::formatInterval($device->uptime);
            } elseif ($device->last_polled) {
                $updowntime = \LibreNMS\Util\Time::formatInterval(time() - strtotime($device->last_polled));
            } else {
                $updowntime = '';
            }

            foreach ($device->parents as $parent) {
                // Keep track of all children for a given device ID
                if (! array_key_exists($parent->device_id, $device_child_map)) {
                    $device_child_map[$parent->device_id] = [];
                }
                $device_child_map[$parent->device_id][$device->device_id] = true;
            }
            if (! count($device->parents)) {
                // This is a top level device
                $next_level_devices[$device->device_id] = true;
            }

            $device_list[$device->device_id] = [
                'id'          => $device->device_id,
                'icon'        => $device->icon,
                'icontitle'   => $device->icon ? str_replace(['.svg', '.png'], '', basename($device->icon)) : $device->os,
                'sname'       => $device->shortDisplayName(),
                'status'      => $device->status,
                'uptime'      => $device->uptime,
                'updowntime'  => $updowntime,
                'last_polled' => $device->last_polled,
                'disabled'    => $device->disabled,
                'no_alerts'   => $device->disable_notify,
                'url'         => $request->get('url_type') == 'links' ? Url::deviceLink($device, null, [], 0, 0, 0, 0) : Url::deviceUrl($device->device_id),
                'style'       => self::deviceStyle($device, $request->get('highlight_node')),
                'lat'         => $device->location ? $device->location->lat : null,
                'lng'         => $device->location ? $device->location->lng : null,
                'parents'     => ($request->get('link_type') == 'depends') ? $device->parents->map->only('device_id')->flatten() : [],
                'maintenance' => array_key_exists($device->device_id, $maintdevicesmap) ? 1 : 0,
            ];
        }

        // Add levels to each device
        $this_level = 0;
        while (count($next_level_devices)) {
            $this_level_devices = $next_level_devices;
            $next_level_devices = [];

            foreach (array_keys($this_level_devices) as $device_id) {
                // Highlight isolated devices if needed
                if ($request->get('highlight_node') == -1 && ! array_key_exists($device_id, $device_child_map) && count($device_list[$device_id]['parents']) == 0) {
                    $device_list[$device_id]['style'] = array_merge($device_list[$device_id]['style'], $this->nodeHighlightStyle());
                }

                // Ignore if the device has already been processed
                if (array_key_exists($device_id, $processed_devices)) {
                    continue;
                }

                // Set device level and mark as processed
                $device_list[$device_id]['level'] = $this_level;
                $processed_devices[$device_id] = true;

                // Add any child devices to be processed next
                if (array_key_exists($device_id, $device_child_map)) {
                    $next_level_devices = $next_level_devices + $device_child_map[$device_id];
                }
            }
            $this_level++;
        }

        // If any device does not have a level it is linked to missing parents, so set to level 0
        foreach (array_keys($device_list) as $device_id) {
            if (! array_key_exists('level', $device_list[$device_id])) {
                $device_list[$device_id]['level'] = 0;
            }
        }

        // Highlight all parents if required
        if ($request->get('showpath') && $request->get('highlight_node') > 0) {
            $processed_parents = [];
            $this_parents = $device_list[$request->get('highlight_node')]['parents'];

            while (count($this_parents) > 0) {
                $next_parents = [];
                foreach ($this_parents as $parent_id) {
                    if (array_key_exists($parent_id, $processed_parents)) {
                        continue;
                    }
                    $processed_parents[$parent_id] = true;

                    $device_list[$parent_id]['style'] = array_merge($device_list[$parent_id]['style'], $this->nodeHighlightStyle());
                    $next_parents = array_merge($next_parents, $device_list[$parent_id]['parents']->toArray());
                }
                $this_parents = $next_parents;
            }
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

            $link_list[$link->left_id . '.' . $link->right_id] = [
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

    // GET Device services
    public function getServices(Request $request)
    {
        $group_id = $request->get('device_group');
        $services = Service::hasAccess($request->user())->with('device');

        if ($group_id) {
            $services->inDeviceGroup($group_id);
        }

        $service_list = [];
        foreach ($services->get() as $service) {
            if ($service->device->status) {
                $updowntime = \LibreNMS\Util\Time::formatInterval($service->device->uptime);
            } elseif ($service->device->last_polled) {
                $updowntime = \LibreNMS\Util\Time::formatInterval(time() - strtotime($service->device->last_polled));
            } else {
                $updowntime = '';
            }

            $service_list[] = [
                'id'          => $service->service_id,
                'name'        => $service->service_name,
                'type'        => $service->service_type,
                'status'      => $service->service_status,
                'icon'        => $service->device->icon,
                'icontitle'   => $service->device->icon ? str_replace(['.svg', '.png'], '', basename($service->device->icon)) : $service->device->os,
                'device_name' => $service->device->shortDisplayName(),
                'url'         => Url::deviceUrl($service->device_id),
                'updowntime'  => $updowntime,
                'compact'     => Config::get('webui.availability_map_compact'),
                'box_size'    => Config::get('webui.availability_map_box_size'),
            ];
        }

        return response()->json($service_list);
    }

    // Availability Map
    public function availabilityMap(Request $request)
    {
        $data = [
            'page_refresh' => Config::get('page_refresh', 300),
            'compact'      => Config::get('webui.availability_map_compact'),
            'box_size'     => Config::get('webui.availability_map_box_size'),
            'sort'         => Config::get('webui.availability_map_sort_status') ? 'status' : 'hostname',
            'use_groups'   => Config::get('webui.availability_map_use_device_groups'),
            'services'     => Config::get('show_services'),
            'uptime_warn'  => Config::get('uptime_warning'),
            'devicegroups' => Config::get('webui.availability_map_use_device_groups') ? DeviceGroup::hasAccess($request->user())->orderBy('name')->get(['id', 'name']) : [],
        ];

        return view('map.availability', $data);
    }

    // Full Screen Map
    public function fullscreenMap(Request $request)
    {
        $group_name = null;
        if ($request->get('group')) {
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
            'netmap_include_disabled_alerts' => Config::get('network_map_worldmap_show_disabled_alerts', true) ? 1 : 0,
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
