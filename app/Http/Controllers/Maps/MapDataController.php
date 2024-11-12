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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class MapDataController extends Controller
{
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
                'url' => $request->url_type == 'links' ? Url::deviceLink($device, null, [], 0, 0, 0, 0) : Url::deviceUrl($device->device_id),
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
                $deviceQuery->where('disabled', '!=', '0');
            } else {
                $deviceQuery->where('disabled', '=', '0');
            }
        }

        if (! is_null($ignore)) {
            if ($ignore) {
                $deviceQuery->where('ignore', '!=', '0');
            } else {
                $deviceQuery->where('ignore', '=', '0');
            }
        }

        if (! is_null($disabled_alerts)) {
            if ($disabled_alerts) {
                $deviceQuery->where('disable_notify', '!=', '0');
            } else {
                $deviceQuery->where('disable_notify', '=', '0');
            }
        }

        if ($valid_loc) {
            $deviceQuery->whereHas('location', function ($q) {
                $q->whereNotNull('lng')
                    ->whereNotNull('lat')
                    ->where('lng', '!=', '')
                    ->where('lat', '!=', '');
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

    /** Apply device filter to the list of local devices that we will load */
    protected function filterDevices($user, $disabled, $disabled_alerts, $group_id): \Closure
    {
        return function ($q) use ($user, $disabled, $disabled_alerts, $group_id) {
            if (! $user->hasGlobalRead()) {
                $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
            }

            if (! is_null($disabled)) {
                $q->where('disabled', $disabled ? '!=' : '=', '0');
            }

            if (! is_null($disabled_alerts)) {
                $q->where('disable_notify', $disabled_alerts ? '!=' : '=', '0');
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
        };
    }
}
