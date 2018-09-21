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
use App\Models\UserWidget;
use DB;
use Illuminate\Http\Request;
use LibreNMS\Config;

class AvailabilityMapController
{
    public function __invoke(Request $request)
    {
        $title = 'Availability Map';
        $widget = UserWidget::find($request->get('id'));

        if ($request->get('settings')) {
            $data = [
                'widget_settings' => $widget->settings,
            ];

            return $this->formatResponse($title, 'widgets.settings.availability-map', $data);
        }

        $device_query = Device::hasAccess($request->user())
            ->select('device_id', 'hostname', 'sysName', 'status', 'uptime');
        if (!$widget->settings['show_disabled_and_ignored']) {
            $device_query->where('disabled');
        }
        $devices = $device_query->get();

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

        return $this->formatResponse($title, 'widgets.availability-map', $data);
    }

    private function formatResponse($title, $view, $data)
    {
        return response()->json([
            'status' => 'ok',
            'title' => $title,
            'html' => view($view, $data)->__toString(),
        ]);
    }
}
