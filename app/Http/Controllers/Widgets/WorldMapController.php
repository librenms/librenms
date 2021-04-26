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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Config;

class WorldMapController extends WidgetController
{
    protected $title = 'World Map';

    public function __construct()
    {
        $this->defaults = [
            'title' => null,
            'title_url' => Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
            'init_lat' => Config::get('leaflet.default_lat', 51.4800),
            'init_lng' => Config::get('leaflet.default_lng', 0),
            'init_zoom' => Config::get('leaflet.default_zoom', 2),
            'group_radius' => Config::get('leaflet.group_radius', 80),
            'status' => '0,1',
            'device_group' => null,
        ];
    }

    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $status = explode(',', $settings['status']);

        $settings['dimensions'] = $request->get('dimensions');

        $devices = Device::hasAccess($request->user())
            ->with('location')
            ->isActive()
            ->whereIn('status', $status)
            ->when($settings['device_group'], function ($query) use ($settings) {
                $query->inDeviceGroup($settings['device_group']);
            })
            ->get()
            ->filter(function ($device) use ($status) {
                /** @var Device $device */
                if (! ($device->location_id && $device->location && $device->location->coordinatesValid())) {
                    return false;
                }

                // add extra data
                /** @phpstan-ignore-next-line */
                $device->markerIcon = 'greenMarker';
                /** @phpstan-ignore-next-line */
                $device->zOffset = 0;

                if ($device->status == 0) {
                    $device->markerIcon = 'redMarker';
                    $device->zOffset = 10000;

                    if ($device->isUnderMaintenance()) {
                        if (in_array(0, $status)) {
                            return false;
                        }
                        $device->markerIcon = 'blueMarker';
                        $device->zOffset = 5000;
                    }
                }

                return true;
            });

        $settings['devices'] = $devices;

        return view('widgets.worldmap', $settings);
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.worldmap', $this->getSettings(true));
    }
}
