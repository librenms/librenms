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

use App\Models\Application;
use App\Models\Bill;
use App\Models\Device;
use App\Models\MuninPlugin;
use App\Models\Port;
use App\Models\UserWidget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Time;
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
        'graph_custom' => [],
        'graph_manual' => null,
        'graph_bill' => null,
    ];

    public function title()
    {
        $settings = $this->getSettings();

        if (!empty($settings['title'])) {
            return $settings['title'];
        }

        // automatic title
        $type = $this->getGraphType();
        if ($type == 'device') {
            $device = Device::find($settings['graph_device']);
            return ($device ? $device->displayName() : 'Device') . ' / ' . $settings['graph_type'];
        } elseif ($type == 'aggregate') {
            return 'Overall ' . $this->getGraphType(false) . ' Bits (' . $settings['graph_range'] . ')';
        } elseif ($type == 'port') {
            if ($port = Port::find($settings['graph_port'])) {
                return $port->device->displayName() . ' / ' . $port->getShortLabel() . ' / ' . $settings['graph_type'];
            }
        } elseif ($type == 'application') {
            if ($application = Application::find($settings['graph_application'])) {
                return $application->device->displayName() . ' / ' . $application->app_type . ' / ' . $settings['graph_type'];
            }
        } elseif ($type == 'bill') {
            if ($bill = Bill::find($settings['graph_bill'])) {
                return $bill->device->displayName() . ' / ' . $bill->bill_name . ' / ' . $settings['graph_type'];
            }
        } elseif ($type == 'munin') {
            if ($munin = MuninPlugin::find($settings['graph_munin'])) {
                return $munin->device->displayName() . ' / ' . $munin->mplug_type . ' / ' . $settings['graph_type'];
            }
        }

        if (empty($widget_settings['title'])) {
            $widget_settings['title']      = $widget_settings['graph_'.$type]['hostname']." / ".$widget_settings['graph_'.$type]['name']." / ".$widget_settings['graph_type'];
        }

        // fall back for types where we couldn't find the item
        if ($settings['graph_type']) {
            return 'Device / ' . ucfirst($type) . ' / ' . $settings['graph_type'];
        }


        return $this->title;
    }

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings();

        // format display name for selected graph type
        $type_parts = explode('_', $data['graph_type']);
        $primary = array_shift($type_parts);
        $secondary = implode('_', $type_parts);
        $name = $primary  . ' ' . (Graph::isMibGraph($primary, $secondary) ? $secondary : implode(' ', $type_parts));

        // format display for selected port
        if ($primary == 'port' && $data['graph_port']) {
            $port = Port::find($data['graph_port']);
        }
        $data['port_text'] = isset($port) ? $port->getLabel() : __('Port does not exist');

        if ($primary == 'application' && $data['graph_application']) {
            $app = Application::find($data['graph_application']);
        }
        $data['application_text'] = isset($app) ? $app->displayName() . ' - ' . $app->device->displayName() : __('App does not exist');

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

        // get type
        $type = $this->getGraphType();

        if ($type == 'device') {
            $param = 'device='.$settings['graph_device'];
        } elseif ($type == 'application') {
            $param = 'id='.$settings['graph_application'];
        } elseif ($type == 'munin') {
            if ($mplug = MuninPlugin::find($settings['graph_munin'])) {
                $param = 'device='.$mplug->device_id.'&plugin='.$mplug->mplug_type;
            }
        } elseif ($type == 'aggregate') {
            $aggregate_type = $this->getGraphType(false);
            if ($aggregate_type == 'custom') {
                $aggregate_type = $settings['graph_custom'];
            }

            $ports = get_ports_from_type($aggregate_type);
            foreach ($ports as $port) {
                $tmp[] = $port['port_id'];
            }
            $param  = 'id='.implode(',', $tmp);
            $settings['graph_type'] = 'multiport_bits_separate';
        } else {
            $param = 'id='.$settings['graph_'.$type];
        }

        $data = $settings;
        $data['param'] = $param;
        $data['dimensions'] = $request->get('dimensions');
        $data['from'] = Carbon::now()->subSeconds(Time::legacyTimeSpecToSecs($settings['graph_range']))->timestamp;
        $data['to'] = Carbon::now()->timestamp;

        return view('widgets.graph', $data);
    }

    private function getGraphType($summarize = true)
    {
        $type = explode('_', $this->getSettings()['graph_type'], 2)[0];

        if ($summarize && in_array($type, ['transit', 'peering', 'core', 'custom'])) {
            return 'aggregate';
        }

        return $type;
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

    public function getSettings()
    {
        if (is_null($this->settings)) {
            $id = \Request::get('id');
            $widget = UserWidget::findOrFail($id);
            $settings = array_replace($this->defaults, (array)$widget->settings);
            $settings['id'] = $id;

            // legacy data conversions
            if ($settings['graph_type'] == 'manual') {
                $settings['graph_type'] = 'custom';
                $settings['graph_custom'] = explode(',', $settings['graph_manual']);
            }
            if ($settings['graph_type'] == 'transpeer') {
                $settings['graph_type'] = 'custom';
                $settings['graph_custom'] = ['transit', 'peer'];
            }

            $settings['graph_device'] = $this->convertLegacySettingId($settings['graph_device'], 'device_id');
            $settings['graph_port'] = $this->convertLegacySettingId($settings['graph_port'], 'port_id');
            $settings['graph_application'] = $this->convertLegacySettingId($settings['graph_application'], 'app_id');
            $settings['graph_munin'] = $this->convertLegacySettingId($settings['graph_munin'], 'mplug_id');
            $settings['graph_bill'] = $this->convertLegacySettingId($settings['graph_device'], 'graph_bill');

            $settings['graph_custom'] = (array)$settings['graph_custom'];


            $this->settings = $settings;
        }

        return $this->settings;
    }

    private function convertLegacySettingId($setting, $key)
    {
        if ($setting && !is_numeric($setting)) {
            $data = json_decode($setting, true);
            return isset($data[$key]) ? $data[$key] : 0;
        }

        return $setting;
    }
}
