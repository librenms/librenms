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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Application;
use App\Models\Bill;
use App\Models\Device;
use App\Models\MuninPlugin;
use App\Models\Port;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Config;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Time;

class GraphController extends WidgetController
{
    protected $title = 'Graph';
    protected $defaults = [
        'title' => null,
        'refresh' => 60,
        'graph_type' => null,
        'graph_range' => 'oneday',
        'graph_legend' => 'yes',
        'graph_device' => null,
        'graph_port' => null,
        'graph_application' => null,
        'graph_munin' => null,
        'graph_service' => null,
        'graph_ports' => [],
        'graph_custom' => [],
        'graph_manual' => null,
        'graph_bill' => null,
    ];

    public function title()
    {
        $settings = $this->getSettings();

        if (! empty($settings['title'])) {
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
                return 'Bill: ' . $bill->bill_name;
            }
        } elseif ($type == 'munin') {
            if ($munin = MuninPlugin::find($settings['graph_munin'])) {
                return $munin->device->displayName() . ' / ' . $munin->mplug_type . ' / ' . $settings['graph_type'];
            }
        } elseif ($type == 'service') {
            if ($service = Service::find($settings['graph_service'])) {
                return $service->device->displayName() . ' / ' . $service->service_type . ' (' . $service->service_desc . ')' . ' / ' . $settings['graph_type'];
            }
        }

        // fall back for types where we couldn't find the item
        if ($settings['graph_type']) {
            return 'Device / ' . ucfirst($type) . ' / ' . $settings['graph_type'];
        }

        return $this->title;
    }

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings(true);

        // format display name for selected graph type
        $type_parts = explode('_', $data['graph_type']);
        $primary = array_shift($type_parts);
        $secondary = implode('_', $type_parts);
        $name = $primary . ' ' . (Graph::isMibGraph($primary, $secondary) ? $secondary : implode(' ', $type_parts));

        // format display for selected items
        if ($primary == 'device' && $data['graph_device']) {
            $device = Device::find($data['graph_device']);
        }
        $data['device_text'] = isset($device) ? $device->displayName() : __('Device does not exist');

        if ($primary == 'port' && $data['graph_port']) {
            $port = Port::find($data['graph_port']);
        }
        $data['port_text'] = isset($port) ? $port->getLabel() : __('Port does not exist');

        if ($primary == 'application' && $data['graph_application']) {
            $app = Application::find($data['graph_application']);
        }
        $data['application_text'] = isset($app) ? $app->displayName() . ' - ' . $app->device->displayName() : __('App does not exist');

        if ($primary == 'bill' && $data['graph_bill']) {
            $bill = Bill::find($data['graph_bill']);
        }
        $data['bill_text'] = isset($bill) ? $bill->bill_name : __('Bill does not exist');

        if ($primary == 'munin' && $data['graph_munin']) {
            $mplug = MuninPlugin::with('device')->find($data['graph_munin']);
        }
        $data['munin_text'] = isset($mplug) ? $mplug->device->displayName() . ' - ' . $mplug->mplug_type : __('Munin plugin does not exist');

        if ($primary == 'service' && $data['graph_service']) {
            $service = Service::with('device')->find($data['graph_service']);
        }
        $data['service_text'] = isset($service) ? $service->device->displayName() . ' - ' . $service->service_type . ' (' . $service->service_desc . ')' : __('Service does not exist');

        $data['graph_ports'] = Port::whereIn('port_id', $data['graph_ports'])
            ->select('ports.device_id', 'port_id', 'ifAlias', 'ifName', 'ifDescr')
            ->with(['device' => function ($query) {
                $query->select('device_id', 'hostname', 'sysName');
            }])->get();

        $data['graph_port_ids'] = $data['graph_ports']->pluck('port_id')->toJson();

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

        // force settings if not initialized
        if ($this->hasInvalidSettings()) {
            return $this->getSettingsView($request);
        }

        $type = $this->getGraphType();
        $params = [];

        if ($type == 'device') {
            $params[] = 'device=' . $settings['graph_device'];
        } elseif ($type == 'application') {
            $params[] = 'id=' . $settings['graph_application'];
        } elseif ($type == 'munin') {
            if ($mplug = MuninPlugin::find($settings['graph_munin'])) {
                $params[] = 'device=' . $mplug->device_id;
                $params[] = 'plugin=' . $mplug->mplug_type;
            }
        } elseif ($type == 'service') {
            if ($service = Service::find($settings['graph_service'])) {
                $params[] = 'device=' . $service->device_id;
                $params[] = 'id=' . $service->service_id;
            }
        } elseif ($type == 'aggregate') {
            $aggregate_type = $this->getGraphType(false);
            if ($aggregate_type == 'custom') {
                $aggregate_type = $settings['graph_custom'];
            }

            if ($aggregate_type == 'ports') {
                $port_ids = $settings['graph_ports'];
            } else {
                $port_types = collect((array) $aggregate_type)->map(function ($type) {
                    // check for config definitions
                    if (Config::has("{$type}_descr")) {
                        return Config::get("{$type}_descr", []);
                    }

                    return $type;
                })->flatten();

                $port_ids = Port::hasAccess($request->user())->where(function ($query) use ($port_types) {
                    foreach ($port_types as $port_type) {
                        $port_type = str_replace('@', '%', $port_type);
                        $query->orWhere('port_descr_type', 'LIKE', $port_type);
                    }
                })->pluck('port_id')->all();
            }

            $params[] = 'id=' . implode(',', $port_ids);
            $settings['graph_type'] = 'multiport_bits_separate';
        } else {
            $params[] = 'id=' . $settings['graph_' . $type];
        }

        $data = $settings;
        $data['params'] = $params;
        $data['dimensions'] = $request->get('dimensions');
        $data['from'] = Carbon::now()->subSeconds(Time::legacyTimeSpecToSecs($settings['graph_range']))->timestamp;
        $data['to'] = Carbon::now()->timestamp;

        return view('widgets.graph', $data);
    }

    private function getGraphType($summarize = true)
    {
        $type = explode('_', $this->getSettings()['graph_type'], 2)[0];

        if ($summarize && in_array($type, ['transit', 'peering', 'core', 'ports', 'custom'])) {
            return 'aggregate';
        }

        return $type;
    }

    private function hasInvalidSettings()
    {
        $raw_type = $this->getGraphType(false);
        if ($raw_type == 'custom' || $this->getGraphType() != 'aggregate') {
            return empty($this->getSettings()['graph_' . $raw_type]);
        }

        return false; // non-custom aggregate types require no additional settings
    }

    public function getSettings($settingsView = false)
    {
        if (is_null($this->settings)) {
            $settings = parent::getSettings($settingsView);

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
            $settings['graph_service'] = $this->convertLegacySettingId($settings['graph_service'], 'service_id');
            $settings['graph_bill'] = $this->convertLegacySettingId($settings['graph_bill'], 'bill_id');

            $settings['graph_custom'] = (array) $settings['graph_custom'];
            $settings['graph_ports'] = (array) $settings['graph_ports'];

            $this->settings = $settings;
        }

        return $this->settings;
    }

    private function convertLegacySettingId($setting, $key)
    {
        if ($setting && ! is_numeric($setting)) {
            $data = json_decode($setting, true);

            return isset($data[$key]) ? $data[$key] : 0;
        }

        return $setting;
    }
}
