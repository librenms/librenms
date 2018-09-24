<?php
/**
 * TopDevices.php
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
use App\Models\Processor;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TopDevicesController extends WidgetController
{
    public $title = 'Top Devices';

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $sort = $settings->get('sort_order', 'asc');
        
        switch($settings->get('top_query', 'traffic')) {
            case 'traffic':
                $data = $this->getTrafficData($sort);
                break;
            case 'uptime':
                $data = $this->getUptimeData($sort);
                break;
            case 'ping':
                $data = $this->getPingData($sort);
                break;
            case 'cpu':
                $data = $this->getProcessorData($sort);
                break;
            case 'ram':
                $data = $this->getMemoryData($sort);
                break;
            case 'poller':
                $data = $this->getPollerData($sort);
                break;
            case 'storage':
                $data = $this->getStorageData($sort);
                break;
            default:
                $data = [];
        }

        return view('widgets.top-devices', $data);
    }

    public function getSettingsView(Request $request)
    {
        $settings = $this->getSettings();

        $data = [
            'id' => $request->get('id'),
            'title' => $request->get('title'),
            'top_query' => $settings->get('top_query', 'traffic'),
            'sort_order' => $settings->get('sort_order'),
            'device_count' => $settings->get('device_count', 5),
            'time_interval' => $settings->get('time_interval', 15),
        ];

        return view('widgets.settings.top-devices', $data);
    }

    private function getTrafficData($sort)
    {
        /** @var Builder $query */
        $query = $this->withDeviceQuery(Port::hasAccess(Auth::user()))
            ->orderByRaw('SUM(ifInOctets_rate + ifOutOctets_rate) ' . $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_bits');
        });

        return $this->formatData('Traffic', $results);
    }

    /**
     * @param array|string $headers
     * @param Collection $rows
     * @return array
     */
    private function formatData($headers, $rows)
    {
        return [
            'headers' => (array)$headers,
            'rows' => $rows,
        ];
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    private function withDeviceQuery($query)
    {
        $settings = $this->getSettings();
        
        return $query->with(['device' => function ($query) {
                $query->select('device_id', 'hostname', 'sysName');
            }])->select('device_id')
            ->where('devices.poll_time', '>', Carbon::now()->subMinutes($settings->get('time_interval', 15)))
            ->groupBy(['device_id'])
            ->limit($settings->get('device_count', 5));
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    private function deviceQuery()
    {
        $settings = $this->getSettings();

        return Device::hasAccess(Auth::user())->select('device_id', 'hostname', 'sysName')
            ->where('devices.poll_time', '>', Carbon::now()->subMinutes($settings->get('time_interval', 15)))
            ->limit($settings->get('device_count', 5));
    }

    /**
     * @param Device $device
     * @param string $graph_type
     * @param array $graph_params
     * @return array
     */
    private function standardRow($device, $graph_type, $graph_params = [])
    {
        $now = Carbon::now();
        return [
            \LibreNMS\Util\Url::deviceLink($device, $device->shortDisplayName()),
            \LibreNMS\Util\Url::deviceLink($device, \LibreNMS\Util\Url::minigraphImage(
                $device,
                $now->subDay()->timestamp,
                $now->timestamp,
                $graph_type,
                'no',
                150,
                21
            ), $graph_params, 0, 0, 0),
        ];
    }

    private function getInfo($type)
    {
        $info = [
            'traffic' => [
                'header' => 'Traffic',
                'graph_type' => 'device_bits',
                'graph_params' => [],
            ],
            'uptime' => [
                'header' => 'Uptime',
                'graph_type' => 'device_uptime',
                'graph_params' => ['tab' => 'graphs', 'group' => 'system'],
            ],
            'ping' => [
                'header' => 'Response time',
                'graph_type' => 'device_ping_perf',
                'graph_params' => ['tab' => 'graphs', 'group' => 'poller'],
            ],
            'cpu' => [
                'header' => 'CPU Load',
                'graph_type' => 'device_processor',
                'graph_params' => ['tab' => 'health', 'metric' => 'processor'],
            ],
            'ram' => [
                'header' => 'Memory usage',
                'graph_type' => 'device_mempool',
                'graph_params' => ['tab' => 'health', 'metric' => 'mempool'],
            ],
            'poller' => [
                'header' => 'Poller duration',
                'graph_type' => 'device_poller_perf',
                'graph_params' => ['tab' => 'graphs', 'group' => 'poller'],
            ],
            'storage' => [
                'header' => 'Disk usage',
                'graph_type' => 'device_storage',
                'graph_params' => ['tab' => 'health', 'metric' => 'storage'],
            ]
        ];

        $result = $info['$type'];
        $result['type'] = $type;

        return $result;
    }

    private function getUptimeData($sort)
    {
        /** @var Builder $query */
        $query = $this->deviceQuery()->orderBy('uptime', $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_uptime', ['tab' => 'graphs', 'group' => 'system']);
        });

        return $this->formatData('Uptime', $results);
    }

    private function getPingData($sort)
    {
        /** @var Builder $query */
        $query = $this->deviceQuery()->orderBy('last_ping_timetaken', $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_ping_perf', ['tab' => 'graphs', 'group' => 'poller']);
        });

        return $this->formatData('Response time', $results);
    }

    private function getProcessorData($sort)
    {
        /** @var Builder $query */
        $query = $this->withDeviceQuery(Processor::hasAccess(Auth::user()))
            ->orderByRaw('AVG(`processor_usage`) ' . $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_processor', ['tab' => 'health', 'metric' => 'processor']);
        });

        return $this->formatData('CPU Load', $results);
    }
}
