<?php
/**
 * MapDataController.php
 *
 * Controller for getting data for maps
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
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\Link;
use App\Models\Port;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;

class MapDataController extends Controller
{
    protected static function geoLinks(Request $request)
    {
        $user = $request->user();

        // Return a blank array for unknown link types
        if ($request->link_type != 'xdp') {
            return collect();
        }

        $linkQuery = Link::with('port', 'device', 'remoteDevice', 'device.location', 'remoteDevice.location')
            ->whereHas('device', function (Builder $q) use ($user) {
                $q->whereIn('status', [0, 1])
                    ->where('disabled', 0)
                    ->where('ignore', 0);

                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }
            })
            ->whereHas('device.location', function (Builder $q) {
                $q->whereNotNull('lat')
                    ->whereNotNull('lng');
            })
            ->whereHas('remoteDevice', function (Builder $q) use ($user) {
                $q->whereIn('status', [0, 1])
                    ->where('disabled', 0)
                    ->where('ignore', 0);

                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }
            })
            ->whereHas('remoteDevice.location', function (Builder $q) {
                $q->whereNotNull('lat')
                    ->whereNotNull('lng');
            })
            ->whereHas('port', function (Builder $q) {
                $q->where('ifOperStatus', 'up');
            });

        $group_id = $request->group;
        if ($group_id) {
            $linkQuery->whereHas('remoteDevice', function ($q) use ($group_id) {
                $q->whereIn('device_id', function ($q) use ($group_id) {
                    $q->select('device_id')
                    ->from('device_group_device')
                    ->where('device_group_id', $group_id);
                });
            })
            ->whereHas('device', function ($q) use ($group_id) {
                $q->whereIn('device_id', function ($q) use ($group_id) {
                    $q->select('device_id')
                    ->from('device_group_device')
                    ->where('device_group_id', $group_id);
                });
            });
        }

        return $linkQuery->get()
            ->groupBy(function (Link $i) {
                return $i->device->location->lat . '.' . $i->device->location->lng . '.' . $i->remoteDevice->location->lat . '.' . $i->remoteDevice->location->lng;
            });
    }

    protected static function portsWithLinks(Request $request, string $remote_port_attr)
    {
        $user = $request->user();
        $disabled = $request->disabled;
        $disabled_alerts = $request->disabled_alerts;
        $group_id = $request->group;
        $device_id = $request->device;

        if (is_null($disabled) && is_null($disabled_alerts) && ! $group_id && $user->hasGlobalRead()) {
            $device_filter = false;
        } else {
            $device_filter = true;
        }

        $linkQuery = Port::hasAccess($request->user())
            ->with([
                $remote_port_attr,
                'device' => function ($q) use ($user, $disabled, $disabled_alerts, $group_id) {
                    // Apply device filter to the list of local devices that we will load
                    if (! $user->hasGlobalRead()) {
                        $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                    }

                    if (! is_null($disabled)) {
                        if ($disabled) {
                            $q->where('disabled', '<>', '0');
                        } else {
                            $q->where('disabled', '=', '0');
                        }
                    }

                    if (! is_null($disabled_alerts)) {
                        if ($disabled_alerts) {
                            $q->where('disable_notify', '<>', '0');
                        } else {
                            $q->where('disable_notify', '=', '0');
                        }
                    }

                    if ($group_id) {
                        $q->whereIn(
                            $q->qualifyColumn('device_id'), function ($q) use ($group_id) {
                                $q->select('device_id')
                                ->from('device_group_device')
                                ->where('device_group_id', $group_id);
                            }
                        );
                    }
                },
                "$remote_port_attr.device" => function ($q) use ($user, $disabled, $disabled_alerts, $group_id) {
                    // Apply device filter to the list of remote devices that we will load
                    if (! $user->hasGlobalRead()) {
                        $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                    }

                    if (! is_null($disabled)) {
                        if ($disabled) {
                            $q->where('disabled', '<>', '0');
                        } else {
                            $q->where('disabled', '=', '0');
                        }
                    }

                    if (! is_null($disabled_alerts)) {
                        if ($disabled_alerts) {
                            $q->where('disable_notify', '<>', '0');
                        } else {
                            $q->where('disable_notify', '=', '0');
                        }
                    }

                    if ($group_id) {
                        $q->whereIn(
                            $q->qualifyColumn('device_id'), function ($q) use ($group_id) {
                                $q->select('device_id')
                                ->from('device_group_device')
                                ->where('device_group_id', $group_id);
                            }
                        );
                    }
                }])
            ->whereHas($remote_port_attr);

        if ($device_filter) {
            // Apply device level filter to the port list so we exclude ports that are not connected to devices we want to display
            $linkQuery->whereHas('device', function (Builder $q) use ($user, $disabled, $disabled_alerts, $group_id) {
                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }

                if (! is_null($disabled)) {
                    if ($disabled) {
                        $q->where('disabled', '<>', '0');
                    } else {
                        $q->where('disabled', '=', '0');
                    }
                }

                if (! is_null($disabled_alerts)) {
                    if ($disabled_alerts) {
                        $q->where('disable_notify', '<>', '0');
                    } else {
                        $q->where('disable_notify', '=', '0');
                    }
                }

                if ($group_id) {
                    $q->whereIn(
                        $q->qualifyColumn('device_id'), function ($q) use ($group_id) {
                            $q->select('device_id')
                            ->from('device_group_device')
                            ->where('device_group_id', $group_id);
                        }
                    );
                }
            });

            // Apply the same device level filter to the port list so we exclude ports that have no remote devices we want to display
            $linkQuery->whereHas("$remote_port_attr.device", function (Builder $q) use ($user, $disabled, $disabled_alerts, $group_id) {
                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }

                if (! is_null($disabled)) {
                    if ($disabled) {
                        $q->where('disabled', '<>', '0');
                    } else {
                        $q->where('disabled', '=', '0');
                    }
                }

                if (! is_null($disabled_alerts)) {
                    if ($disabled_alerts) {
                        $q->where('disable_notify', '<>', '0');
                    } else {
                        $q->where('disable_notify', '=', '0');
                    }
                }

                if ($group_id) {
                    $q->whereIn(
                        $q->qualifyColumn('device_id'), function ($q) use ($group_id) {
                            $q->select('device_id')
                            ->from('device_group_device')
                            ->where('device_group_id', $group_id);
                        }
                    );
                }
            });
        }

        if ($device_id) {
            // If we have a device ID, we want to show if we are the soure or target of a link
            $linkQuery->where(function ($q) use ($device_id, $remote_port_attr) {
                $q->whereHas($remote_port_attr, function ($q) use ($device_id) {
                    $q->where('device_id', $device_id);
                })
                    ->orWhereHas('device', function ($q) use ($device_id) {
                        $q->where('device_id', $device_id);
                    });
            });
        }

        return $linkQuery->get();
    }

    protected static function deviceList(Request $request)
    {
        $group_id = $request->group;
        $devices = $request->devices;
        $valid_loc = $request->location_valid;
        $disabled = $request->disabled;
        $ignore = $request->ignore;
        $disabled_alerts = $request->disabled_alerts;
        $linkType = $request->link_type;
        $statuses = $request->statuses;

        $deviceQuery = Device::hasAccess($request->user())->with('location');

        if ($group_id) {
            $deviceQuery->inDeviceGroup($group_id);
        }

        if ($devices) {
            $deviceQuery->whereIntegerInRaw('device_id', $devices);
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
            $deviceQuery->whereHas('location', function ($q) {
                $q->whereNotNull('lng')
                    ->whereNotNull('lat')
                    ->where('lng', '<>', '')
                    ->where('lat', '<>', '');
            });
        }

        if (! $group_id) {
            if ($linkType == 'depends') {
                return $deviceQuery->with([
                    'parents' => function ($q) use ($request) {
                        $q->hasAccess($request->user());
                    },
                    'children' => function ($q) use ($request) {
                        $q->hasAccess($request->user());
                    }, ])
                ->get();
            } else {
                return $deviceQuery->get();
            }
        }

        if ($linkType == 'depends') {
            return $deviceQuery->with([
                'parents' => function ($q) use ($request, $group_id) {
                    $q->hasAccess($request->user())
                        ->inDeviceGroup($group_id);
                },
                'children' => function ($q) use ($request, $group_id) {
                    $q->hasAccess($request->user())
                        ->inDeviceGroup($group_id);
                }, ])
            ->get();
        } else {
            return $deviceQuery->get();
        }
    }

    protected function linkSpeedWidth(int|null $speed): int
    {
        $speed /= 10000000;
        if (is_nan($speed)) {
            return 1;
        }
        if ($speed < 1) {
            return 1;
        }

        return strlen(strval(round($speed))) * 2;
    }

    protected function linkUseColour(float $link_pct): string
    {
        $link_pct = round(2 * $link_pct, -1) / 2;
        if ($link_pct > 100) {
            $link_pct = 100;
        }
        if (is_nan($link_pct)) {
            $link_pct = 0;
        }

        return Config::get("network_map_legend.$link_pct", '#000000');
    }

    protected function nodeDisabledStyle(): array
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

    protected function nodeHighlightStyle(): array
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

    protected function nodeDownStyle(): array
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

    protected function nodeUpStyle(): array
    {
        return [
            'color' => null,
            'border' => null,
            'background' => null,
            'borderWidth' => null,
        ];
    }

    protected function deviceStyle($device, $highlight_node = 0): array
    {
        if ($device->disabled) {
            $device_style = $this->nodeDisabledStyle();
        } elseif (! $device->status) {
            $device_style = $device->disable_notify ? $this->nodeDisabledStyle() : $this->nodeDownStyle();
        } else {
            $device_style = $this->nodeUpStyle();
        }

        if ($device->device_id == $highlight_node) {
            $device_style = array_merge($device_style, $this->nodeHighlightStyle());
        }

        return $device_style;
    }

    // GET Device
    public function getDevices(Request $request): JsonResponse
    {
        // Get all device ids under maintenance (may contain duplicates, but we don't care for this usage)
        $deviceIdsUnderMaintenance = AlertSchedule::isActive()
            ->with([
                'devices:device_id',
                'locations.devices:location_id,device_id',
                'deviceGroups.devices:device_id',
            ])->get()
            ->map(function ($schedule) {
                return $schedule->devices->pluck('device_id')
                    ->merge($schedule->locations->pluck('devices.*.device_id'))
                    ->merge($schedule->deviceGroups->pluck('devices.*.device_id'));
            })->flatten();

        // For manual level we need to track some items
        $no_parent_devices = collect();
        $parent_peer_devices = collect();
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

            $device_list[$device->device_id] = [
                'id' => $device->device_id,
                'icon' => $device->icon,
                'icontitle' => $device->icon ? str_replace(['.svg', '.png'], '', basename($device->icon)) : $device->os,
                'sname' => $device->shortDisplayName(),
                'status' => $device->status,
                'uptime' => $device->uptime,
                'updowntime' => $updowntime,
                'last_polled' => $device->last_polled,
                'disabled' => $device->disabled,
                'no_alerts' => $device->disable_notify,
                'url' => $request->url_type == 'links' ? \Blade::render('<x-device-link-map :device="$device" />', ['device' => $device]) : route('device', ['device' => $device->device_id]),
                'style' => self::deviceStyle($device, $request->highlight_node),
                'lat' => $device->location ? $device->location->lat : null,
                'lng' => $device->location ? $device->location->lng : null,
                'parents' => ($request->link_type == 'depends') ? $device->parents->pluck('device_id', 'device_id') : collect(),
                'children' => ($request->link_type == 'depends') ? $device->children->pluck('device_id', 'device_id') : collect(),
                'maintenance' => $deviceIdsUnderMaintenance->contains($device->device_id) ? 1 : 0,
            ];

            // Only use parent IDs below if it is being returned
            $parent_ids = $device_list[$device->device_id]['parents'];

            if (! $parent_ids->count()) {
                // No parents
                $no_parent_devices->put($device->device_id, $device->device_id);
            } else {
                // Get a list of parents that are not direct children
                $parent_only_ids = $parent_ids;
                if ($device->children->count()) {
                    $child_ids = $device_list[$device->device_id]['children'];
                    $parent_only_ids = $parent_only_ids->filter(function (int $parent_id, int $k) use ($child_ids) {
                        return ! $child_ids->has($parent_id);
                    });
                }

                // All parents are peers becuase they are also children
                if (! $parent_only_ids->count()) {
                    $parent_peer_devices->put($device->device_id, $device->device_id);
                }
            }
        }

        if ($request->link_type == 'depends') {
            // Check multiple lists of possible top level devices.
            // If a device is found in the tree of the first list, it will be ignored on subsequent checks.
            $top_level_check = [$no_parent_devices, $parent_peer_devices];

            foreach ($top_level_check as $next_level_devices) {
                // Start at level 0 each time
                $this_level = 0;

                while ($next_level_devices->count()) {
                    $this_level_devices = $next_level_devices;
                    $next_level_devices = collect();

                    foreach ($this_level_devices->keys() as $device_id) {
                        // Ignore if this device is not in the returned array
                        if (! array_key_exists($device_id, $device_list)) {
                            continue;
                        }

                        // Ignore if the device has already been processed
                        if (array_key_exists($device_id, $processed_devices)) {
                            continue;
                        }

                        $device_record = $device_list[$device_id];

                        // Highlight isolated devices if needed
                        if ($request->highlight_node == -1 && $device_record['children']->count() === 0 && $device_record['parents']->count() == 0) {
                            $device_list[$device_id]['style'] = array_merge($device_list[$device_id]['style'], $this->nodeHighlightStyle());
                        }

                        // Set device level and mark as processed
                        $device_list[$device_id]['level'] = $this_level;
                        $processed_devices[$device_id] = true;

                        // Add any child devices to be processed next
                        $next_level_devices = $next_level_devices->union($device_list[$device_id]['children']);
                    }
                    $this_level++;
                }
            }

            // If any device does not have a level it is linked to missing parents, so set to level 0
            foreach (array_keys($device_list) as $device_id) {
                if (! array_key_exists('level', $device_list[$device_id])) {
                    $device_list[$device_id]['level'] = 0;
                }
            }

            if ($request->showpath > 0 && $request->highlight_node > 0) {
                // Highlight all parents if required
                $processed_parents = [];
                $this_parents = $device_list[$request->highlight_node]['parents'];

                while ($this_parents->count() > 0) {
                    $next_parents = collect();
                    foreach ($this_parents as $parent_id) {
                        if (array_key_exists($parent_id, $processed_parents)) {
                            continue;
                        }
                        $processed_parents[$parent_id] = true;

                        $device_list[$parent_id]['style'] = array_merge($device_list[$parent_id]['style'], $this->nodeHighlightStyle());
                        $next_parents = $next_parents->union($device_list[$parent_id]['parents']);
                    }
                    $this_parents = $next_parents;
                }
            } elseif ($request->showpath < 0 && $request->highlight_node > 0) {
                // Highlight all children if required
                $processed_children = [];
                $this_children = $device_list[$request->highlight_node]['children'];

                while ($this_children->count() > 0) {
                    $next_children = collect();
                    foreach ($this_children as $child_id) {
                        // Ignore if a child is found that has been filtered from the device list
                        if (! array_key_exists($child_id, $device_list)) {
                            continue;
                        }
                        if (array_key_exists($child_id, $processed_children)) {
                            continue;
                        }
                        $processed_children[$child_id] = true;

                        $device_list[$child_id]['style'] = array_merge($device_list[$child_id]['style'], $this->nodeHighlightStyle());
                        $next_children = $next_children->union($device_list[$child_id]['children']);
                    }
                    $this_children = $next_children;
                }
            }
        }

        return response()->json($device_list);
    }

    // GET Device Links by device
    public function getDeviceLinks(Request $request): JsonResponse
    {
        // List all links
        $link_list = [];
        $port_assoc_seen = [];
        $link_types = $request->link_types;

        foreach ($link_types as $link_type) {
            if ($link_type == 'mac') {
                $remote_port_attr = 'macLinkedPorts';
            } elseif ($link_type == 'xdp') {
                $remote_port_attr = 'xdpLinkedPorts';
            } else {
                Log::error("Link types of $link_type are not supported");
                abort(500);
            }

            foreach (self::portsWithLinks($request, $remote_port_attr) as $port) {
                // Ignore any entries if the device has not been loaded (filtered out)
                if (! $port->device) {
                    continue;
                }

                foreach ($port->{$remote_port_attr} as $remote_port) {
                    // Ignore any entries if the device has not been loaded (filtered out)
                    if (! $remote_port->device) {
                        continue;
                    }

                    if ($port->port_id < $remote_port->port_id) {
                        $port_ids = $port->port_id . '.' . $remote_port->port_id;
                    } else {
                        $port_ids = $remote_port->port_id . '.' . $port->port_id;
                    }

                    // Ignore any associations that have already been processed
                    if (array_key_exists($port_ids, $port_assoc_seen)) {
                        continue;
                    }
                    $port_assoc_seen[$port_ids] = true;

                    $width = $this->linkSpeedWidth($port->ifSpeed);

                    if ($port->device->status == 0 && $remote_port->device->status == 0) {
                        // If both devices are offline, mark the link as being down
                        $link_style = [
                            'dashes' => [8, 12],
                            'width' => $width,
                            'color' => [
                                'border' => Config::get('network_map_legend.dn.border'),
                                'highlight' => Config::get('network_map_legend.dn.edge'),
                                'color' => Config::get('network_map_legend.dn.edge'),
                            ],
                        ];
                    } elseif ($port->ifOperStatus == 'down' || $remote_port->ifOperStatus == 'down') {
                        // If either port is offline, mark the link as being down
                        $link_style = [
                            'dashes' => [8, 12],
                            'width' => $width,
                            'color' => [
                                'border' => Config::get('network_map_legend.dn.border'),
                                'highlight' => Config::get('network_map_legend.dn.edge'),
                                'color' => Config::get('network_map_legend.dn.edge'),
                            ],
                        ];
                    } else {
                        if ($port->ifSpeed > 0) {
                            $link_in_usage_pct = $port->ifInOctets_rate * 8 / $port->ifSpeed * 100;
                            $link_out_usage_pct = $port->ifOutOctets_rate * 8 / $port->ifSpeed * 100;
                            $link_used = max($link_out_usage_pct, $link_in_usage_pct);
                        } else {
                            $link_used = 0;
                        }
                        $link_color = $this->linkUseColour($link_used);
                        $link_style = [
                            'width' => $width,
                            'color' => [
                                'border' => $link_color,
                                'highlight' => $link_color,
                                'color' => $link_color,
                            ],
                        ];
                    }

                    $link_list[$port->port_id . '.' . $remote_port->port_id] = [
                        'ldev' => $port->device_id,
                        'rdev' => $remote_port->device_id,
                        'ifnames' => $port->ifName . ' <> ' . $remote_port->ifName,
                        'url' => \Blade::render('<x-port-link-map :port="$port" />', ['port' => $port]),
                        'style' => $link_style,
                    ];
                }
            }
        }

        return response()->json($link_list);
    }

    // GET Device Links grouped by geographic locations
    public function getGeographicLinks(Request $request): JsonResponse
    {
        // List all links
        $link_list = [];
        foreach (self::geoLinks($request) as $location) {
            $link = $location[0];
            $capacity = $location->sum(function (Link $l) {
                return $l->port->ifSpeed;
            });
            $inRate = $location->sum(function (Link $l) {
                return $l->port->ifInOctets_rate * 8;
            });
            $outRate = $location->sum(function (Link $l) {
                return $l->port->ifOutOctets_rate * 8;
            });
            if ($capacity > 0) {
                $link_used = max($inRate / $capacity * 100, $outRate / $capacity * 100);
            } elseif ($inRate > 0 || $outRate > 0) {
                $link_used = 100;
            } else {
                $link_used = 0;
            }

            $width = $this->linkSpeedWidth($capacity);
            $link_color = $this->linkUseColour($link_used);

            $link_list[$link->device->location_id . '.' . $link->remoteDevice->location_id] = [
                'local_lat' => $link->device->location->lat,
                'local_lng' => $link->device->location->lng,
                'remote_lat' => $link->remoteDevice->location->lat,
                'remote_lng' => $link->remoteDevice->location->lng,
                'color' => $link_color,
                'width' => $width,
            ];
        }

        return response()->json($link_list);
    }

    // GET Device services
    public function getServices(Request $request): JsonResponse
    {
        $group_id = $request->device_group;
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
                'id' => $service->service_id,
                'name' => $service->service_name,
                'type' => $service->service_type,
                'status' => $service->service_status,
                'icon' => $service->device->icon,
                'icontitle' => $service->device->icon ? str_replace(['.svg', '.png'], '', basename($service->device->icon)) : $service->device->os,
                'device_name' => $service->device->shortDisplayName(),
                'url' => \Blade::render('<x-device-link-map :device="$device" />', ['device' => $service->device]),
                'updowntime' => $updowntime,
                'compact' => Config::get('webui.availability_map_compact'),
                'box_size' => Config::get('webui.availability_map_box_size'),
            ];
        }

        return response()->json($service_list);
    }
}
