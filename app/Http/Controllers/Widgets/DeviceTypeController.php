<?php
/**
 * DeviceSummaryController.php
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
use Illuminate\Http\Request;
use LibreNMS\DB\Eloquent;

class DeviceTypeController extends WidgetController
{
    protected $title = 'Device Types';

    public function __construct()
    {
        // init defaults we need to check config, so do it in construct
        $this->defaults = [
            'top_device_group_count' => 5,
            'sort_order' => 'name',
        ];
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.device-types', $this->getSettings(true));
    }

    protected function getData(Request $request): array
    {
        $data = $this->getSettings();

        $counts = Device::groupBy(['type'])->select('type', Eloquent::DB()->raw('COUNT(*) as total'))->orderByDesc('total')->pluck('total', 'type');

        if ($data['top_device_group_count']) {
            $top = $counts->take($data['top_device_group_count']);
        } else {
            $top = $counts;
        }

        $count = 0;
        $device_types = [];
        foreach (\LibreNMS\Config::get('device_types') as $device_type) {
            $count++;
            $device_types[] = [
                'type' => $device_type['type'],
                'count' => $counts->get($device_type['type'], 0),
                'visible' => $top->has($device_type['type']) || (! $data['top_device_group_count'] || $count < $data['top_device_group_count']),
            ];
        }

        if ($data['sort_order'] == 'name') {
            usort($device_types, function ($item1, $item2) {
                return $item1['type'] <=> $item2['type'];
            });
        } else {
            usort($device_types, function ($item1, $item2) {
                return $item2['count'] <=> $item1['count'];
            });
        }

        $data['device_types'] = $device_types;

        return $data;
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function getView(Request $request)
    {
        return view('widgets.device-types', $this->getData($request));
    }
}
