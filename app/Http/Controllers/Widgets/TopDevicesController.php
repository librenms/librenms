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
use App\Models\Mempool;
use App\Models\Port;
use App\Models\Processor;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LibreNMS\Util\Colors;
use LibreNMS\Util\Html;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Url;

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
                $query->select('devices.device_id', 'devices.hostname', 'devices.sysName');
            }])->select('devices.device_id')
            ->where('devices.last_polled', '>', Carbon::now()->subMinutes($settings->get('time_interval', 15)))
            ->groupBy(['devices.device_id'])
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
            Url::deviceLink($device, $device->shortDisplayName()),
            Url::deviceLink($device, Url::minigraphImage(
                $device,
                $now->subDay()->timestamp,
                $now->timestamp,
                $graph_type,
                'no',
                150,
                210
            ), $graph_params, 0, 0, 0),
        ];
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

    private function getMemoryData($sort)
    {
        /** @var Builder $query */
        $query = $this->withDeviceQuery(Mempool::hasAccess(Auth::user()))
            ->orderBy('mempool_perc', $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_mempool', ['tab' => 'health', 'metric' => 'mempool']);
        });

        return $this->formatData('Memory usage', $results);
    }

    private function getPollerData($sort)
    {
        /** @var Builder $query */
        $query = $this->deviceQuery()->orderBy('last_polled_timetaken', $sort);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_poller_perf', ['tab' => 'graphs', 'group' => 'poller']);
        });

        return $this->formatData('Poller duration', $results);
    }

    private function getStorageData($sort)
    {
        $settings = $this->getSettings();
        $now = Carbon::now();

        /** @var Builder $query */
        $query = \App\Models\Storage::hasAccess(Auth::user())->with(['device' => function ($query) {
                $query->select('devices.device_id', 'devices.hostname', 'devices.sysName');
            }])
            ->leftJoin('devices', 'storage.device_id', 'devices.device_id')
            ->select('storage.device_id', 'storage_id', 'storage_descr', 'storage_perc', 'storage_perc_warn')
            ->where('devices.last_polled', '>', $now->subMinutes($settings->get('time_interval', 15)))
            ->orderBy('storage_perc', $sort)
            ->limit($settings->get('device_count', 5));


        $results = $query->get()->map(function ($storage) use ($now) {
            $device = $storage->device;

            $graph_array = [
                'height' => 100,
                'width' => 210,
                'to' => $now->timestamp,
                'from' => $now->subDay()->timestamp,
                'id' => $storage->storage_id,
                'type' => 'device_storage',
                'legend' => 'no',
            ];
            $overlib_content = Url::overlibContent($graph_array, $device->displayName() . ' - ' . $storage->storage_descr);

            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = Url::generate($link_array);

            $percent = $storage->storage_perc;
            $background = Colors::percentage($percent, $storage->storage_perc_warn);

            return [
                Url::deviceLink($device, $device->shortDisplayName()),
                StringHelpers::shortenText($storage->storage_descr, 50),
                Url::overlibLink(
                    $link,
                    Html::percentageBar(150, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']),
                    $overlib_content
                )
            ];
        });

        return $this->formatData(['Storage Device', 'Disk usage'], $results);
    }
}
