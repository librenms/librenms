<?php
/**
 * WorldMapController.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class WorldMapController extends WidgetController
{
    protected $title = 'World Map';

    public function __construct()
    {
        $this->defaults = [
            'title' => null,
            'init_lat' => Config::get('leaflet.default_lat'),
            'init_lng' => Config::get('leaflet.default_lng'),
            'init_zoom' => Config::get('leaflet.default_zoom'),
            'init_layer' => Config::get('geoloc.layer'),
            'group_radius' => Config::get('leaflet.group_radius'),
            'status' => '0,1',
            'device_group' => null,
        ];
    }

    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $settings['dimensions'] = $request->get('dimensions');
        $settings['status'] = array_map('intval', explode(',', $settings['status']));
        $settings['map_config'] = [
            'engine' => Config::get('geoloc.engine'),
            'api_key' => Config::get('geoloc.api_key'),
            'tile_url' => Config::get('leaflet.tile_url'),
            'lat' => $settings['init_lat'],
            'lng' => $settings['init_lng'],
            'zoom' => $settings['init_zoom'],
            'layer' => $settings['init_layer'],
        ];

        return view('widgets.worldmap', $settings);
    }

    public function getData(Request $request): JsonResponse
    {
        $this->validate($request, [
            'status' => 'array',
            'status.*' => 'int',
            'device_group' => 'int',
        ]);

        return response()->json($this->getMarkerData($request, $request->status ?? [0, 1], $request->device_group ?? 0));
    }

    public function getMarkerData(Request $request, array $status, int $device_group_id): array
    {
        return Device::hasAccess($request->user())
            ->with('location')
            ->isActive()
            ->whereIn('status', $status)
            ->when($device_group_id, fn ($q) => $q->inDeviceGroup($device_group_id))
            ->get()
            ->filter(function ($device) use ($status) {
                /** @var Device $device */
                if (! ($device->location_id && $device->location && $device->location->coordinatesValid())) {
                    return false;
                }

                // hide devices under maintenance if only showing down devices
                if ($status == [0] && $device->isUnderMaintenance()) {
                    return false;
                }

                return true;
            })->map(function (Device $device) {
                return [
                    'name' => $device->displayName(),
                    'lat' => $device->location->lat,
                    'lng' => $device->location->lng,
                    'icon' => $device->icon,
                    'url' => Url::deviceUrl($device),
                    // status: 0 = down, 1 = up, 3 = down + under maintenance
                    'status' => (int) ($device->status ?: ($device->isUnderMaintenance() ? 3 : 0)),
                ];
            })->values()->all();
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.worldmap', $this->getSettings(true));
    }
}
