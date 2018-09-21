<?php
/**
 * AvailabilityMapController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\UserWidget;
use DB;
use Illuminate\Http\Request;
use LibreNMS\Config;

class AvailabilityMapController
{
    public function __invoke(Request $request)
    {
        $title = 'Availability Map';
        $id = $request->get('id');
        $widget = UserWidget::find($id);
        $settings = collect($widget->settings);

        if ($request->get('settings')) {
            $data = [
                'id' => $id,
                'title' => $settings->get('title'),
                'tile_size' => $settings->get('tile_size'),
                'color_only_select' => $settings->get('color_only_select'),
                'show_disabled_and_ignored' => $settings->get('show_disabled_and_ignored'),
                'mode_select' => $settings->get('mode_select'),
                'device_group' => DeviceGroup::find($settings->get('device_group')),
            ];

            return $this->formatResponse($title, 'widgets.settings.availability-map', $data, $settings);
        }

        // filter for by device group or show all
        if ($group_id = $settings->get('device_group')) {
            $device_query = DeviceGroup::find($group_id)->devices();
        } else {
            $device_query = Device::hasAccess($request->user());
        }

        if (!$settings->get('show_disabled_and_ignored')) {
            $device_query->where('disabled', 0);
        }
        $devices = $device_query->select('devices.device_id', 'hostname', 'sysName', 'status', 'uptime')->get();

        // count status
        $uptime_warn = Config::get('uptime_warning', 84600);
        $totals = ['warn' => 0, 'up' => 0, 'down' => 0];
        foreach ($devices as $device) {
            if ($device->status == 1) {
                if (($device->uptime < $uptime_warn) && ($device->uptime != 0)) {
                    $totals['warn']++;
                } else {
                    $totals['up']++;
                }
            } else {
                $totals['down']++;
            }
        }
        $data['totals'] = $totals;

        $data = compact('devices', 'totals');

        return $this->formatResponse($title, 'widgets.availability-map', $data, $settings);
    }

    private function formatResponse($title, $view, $data, $settings)
    {
        return response()->json([
            'status' => 'ok',
            'title' => $title,
            'html' => view($view, $data)->__toString(),
            'settings' => $settings,
        ]);
    }
}
