<?php
/**
 * GraphController.php
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
use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Url;

class GraphController extends WidgetController
{
    protected $title = 'Graph';
    protected $defaults = [
        'title' => null,
        'graph_type' => null,
        'graph_range' => 'oneday',
        'graph_legend' => 'yes',
        'graph_device' => null,
        'graph_port' => null,
        'graph_application' => null,
        'graph_munin' => null,
        'graph_custom' => null,
        'graph_manual' => null,
        'graph_bill' => null,
    ];

    public function title()
    {
        $settings = $this->getSettings();
        return isset($settings['title']) ? $settings['title'] : $this->title;
    }

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings();

        // format display name for selected item
        $type_parts = explode('_', $data['graph_type']);
        $primary = array_shift($type_parts);
        $secondary = implode('_', $type_parts);
        $name = $primary  . ' ' . (Graph::isMibGraph($primary, $secondary) ? $secondary : implode(' ', $type_parts));

        $data['graph_text'] = ucwords($name);

        return view('widgets.settings.graph', $data);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $settings = $this->getSettings();

        $data = [
            'graph_image' => ''
        ];

        if (starts_with($settings['graph_type'], 'port_')) {
            $data['graph_image'] = $this->getPortGraph($request);
        } elseif(starts_with($settings['graph_type'], 'device_')) {
            $data['graph_image'] = $this->getDeviceGraph($request);
        }


        return view('widgets.graph', $data);
    }

    private function getPortGraph(Request $request)
    {
        $settings = $this->getSettings();
        $dimensions = $request->get('dimensions');

        $port_data = json_decode($settings['graph_port'], true);
        $port = Port::find(is_array($port_data) ? $port_data['port_id'] : $settings['graph_port']);
        if (!$port) {
            return __('Port not found');
        }

        $time_offset = \LibreNMS\Util\Time::legacyTimeSpecToSecs($settings['graph_range']);

        $graph_array = [
            'type' => $settings['graph_type'] ?: 'port_bits',
            'legend' => $settings['graph_legend'],
            'width' => $dimensions['x'],
            'height' => $dimensions['y'],
            'to' => Carbon::now()->timestamp,
            'from' => Carbon::now()->subSeconds($time_offset)->timestamp,
            'id' => $port->port_id,
        ];
        $graph = Url::graphTag($graph_array);
        return Url::portLink($port, $graph);
    }

    private function getDeviceGraph(Request $request)
    {
        $settings = $this->getSettings();
        $dimensions = $request->get('dimensions');

        $device_data = json_decode($settings['graph_device'], true);
        $device = Device::find(is_array($device_data) ? $device_data['device_id'] : $settings['graph_device']);
        if (!$device) {
            return __('Device not found');
        }

        $time_offset = \LibreNMS\Util\Time::legacyTimeSpecToSecs($settings['graph_range']);

        $graph_array = [
            'type' => $settings['graph_type'] ?: 'device_bits',
            'legend' => $settings['graph_legend'],
            'width' => $dimensions['x'],
            'height' => $dimensions['y'],
            'to' => Carbon::now()->timestamp,
            'from' => Carbon::now()->subSeconds($time_offset)->timestamp,
            'id' => $device ? $device->device_id : 0,
        ];
        $graph = Url::graphTag($graph_array);
        return Url::deviceLink($device, $graph);
    }
}
