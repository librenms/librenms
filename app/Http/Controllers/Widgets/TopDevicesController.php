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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\Port;
use App\Models\Processor;
use App\Models\Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use LibreNMS\Util\Html;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Url;
use LibreNMS\Util\Validate;

class TopDevicesController extends WidgetController
{
    protected $title = 'Top Devices';
    protected $defaults = [
        'title' => null,
        'top_query' => 'traffic',
        'sort_order' => 'desc',
        'device_count' => 5,
        'time_interval' => 15,
        'device_group' => null,
    ];

    public function title()
    {
        $settings = $this->getSettings();

        return isset($settings['title']) ? $settings['title'] : $this->title;
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $sort = $settings['sort_order'];

        // We use raw() function below, validate input and default to sane value.
        $sort = Validate::ascDesc($sort, 'ASC');

        switch ($settings['top_query']) {
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
        return view('widgets.settings.top-devices', $this->getSettings(true));
    }

    /**
     * @param array|string $headers
     * @param Collection $rows
     * @return array
     */
    private function formatData($headers, $rows)
    {
        return [
            'headers' => (array) $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @param Builder $query
     * @param string $left_table
     * @return Builder
     */
    private function withDeviceQuery(Builder $query, $left_table)
    {
        $settings = $this->getSettings();

        /** @var Builder $query */
        return $query->with(['device' => function ($query) {
            $query->select('device_id', 'hostname', 'sysName', 'status', 'os');
        }])
            ->select("$left_table.device_id")
            ->leftJoin('devices', "$left_table.device_id", 'devices.device_id')
            ->groupBy("$left_table.device_id")
            ->where('devices.last_polled', '>', Carbon::now()->subMinutes($settings['time_interval']))
            ->when($settings['device_group'], function ($query) use ($settings) {
                /** @var Builder<\App\Models\DeviceRelatedModel> $query */
                return $query->inDeviceGroup($settings['device_group']);
            });
    }

    /**
     * @return Builder
     */
    private function deviceQuery()
    {
        $settings = $this->getSettings();

        return Device::hasAccess(Auth::user())->select('device_id', 'hostname', 'sysName', 'status', 'os')
            ->where('devices.last_polled', '>', Carbon::now()->subMinutes($settings['time_interval']))
            ->when($settings['device_group'], function ($query) use ($settings) {
                $query->inDeviceGroup($settings['device_group']);
            })
            ->limit($settings['device_count']);
    }

    /**
     * @param Device $device
     * @param string $graph_type
     * @param array $graph_params
     * @return array
     */
    private function standardRow($device, $graph_type, $graph_params = [])
    {
        return [
            Url::deviceLink($device, $device->shortDisplayName()),
            Url::deviceLink($device, Url::minigraphImage(
                $device,
                Carbon::now()->subDays(1)->timestamp,
                Carbon::now()->timestamp,
                $graph_type,
                'no',
                150,
                21
            ), $graph_params, 0, 0, 0),
        ];
    }

    private function getTrafficData($sort)
    {
        $settings = $this->getSettings();

        $query = Port::hasAccess(Auth::user())->with(['device' => function ($query) {
            $query->select('device_id', 'hostname', 'sysName', 'status', 'os');
        }])
            ->select('device_id')
            ->groupBy('device_id')
            ->where('poll_time', '>', Carbon::now()->subMinutes($settings['time_interval'])->timestamp)
            ->when($settings['device_group'], function ($query) use ($settings) {
                $query->inDeviceGroup($settings['device_group']);
            }, function ($query) {
                $query->has('device');
            })
            ->orderByRaw('SUM(ifInOctets_rate + ifOutOctets_rate) ' . $sort)
            ->limit($settings['device_count']);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_bits');
        });

        return $this->formatData('Traffic', $results);
    }

    private function getUptimeData($sort)
    {
        $settings = $this->getSettings();

        /** @var Builder $query */
        $query = $this->deviceQuery()->orderBy('uptime', $sort)->limit($settings['device_count']);

        $results = $query->get()->map(function ($device) {
            /** @var Device $device */
            return $this->standardRow($device, 'device_uptime', ['tab' => 'graphs', 'group' => 'system']);
        });

        return $this->formatData('Uptime', $results);
    }

    private function getPingData($sort)
    {
        $settings = $this->getSettings();

        /** @var Builder $query */
        $query = $this->deviceQuery()->orderBy('last_ping_timetaken', $sort)->limit($settings['device_count']);

        $results = $query->get()->map(function ($device) {
            /** @var Device $device */
            return $this->standardRow($device, 'device_ping_perf', ['tab' => 'graphs', 'group' => 'poller']);
        });

        return $this->formatData('Response time', $results);
    }

    private function getProcessorData($sort)
    {
        $settings = $this->getSettings();

        /** @var Processor $query */
        $query = $this->withDeviceQuery(Processor::hasAccess(Auth::user()), (new Processor)->getTable())
            ->orderByRaw('AVG(`processor_usage`) ' . $sort)
            ->limit($settings['device_count']);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_processor', ['tab' => 'health', 'metric' => 'processor']);
        });

        return $this->formatData('CPU Load', $results);
    }

    private function getMemoryData($sort)
    {
        $settings = $this->getSettings();

        /** @var Mempool $query */
        $query = $this->withDeviceQuery(Mempool::hasAccess(Auth::user()), (new Mempool)->getTable())
            ->orderBy('mempool_perc', $sort)
            ->limit($settings['device_count']);

        $results = $query->get()->map(function ($port) {
            return $this->standardRow($port->device, 'device_mempool', ['tab' => 'health', 'metric' => 'mempool']);
        });

        return $this->formatData('Memory usage', $results);
    }

    private function getPollerData($sort)
    {
        $settings = $this->getSettings();

        $query = $this->deviceQuery()->orderBy('last_polled_timetaken', $sort)->limit($settings['device_count']);

        $results = $query->get()->map(function ($device) {
            /** @var Device $device */
            return $this->standardRow($device, 'device_poller_perf', ['tab' => 'graphs', 'group' => 'poller']);
        });

        return $this->formatData('Poller duration', $results);
    }

    private function getStorageData($sort)
    {
        $settings = $this->getSettings();

        $query = Storage::hasAccess(Auth::user())->with(['device' => function ($query) {
            $query->select('device_id', 'hostname', 'sysName', 'status', 'os');
        }])
            ->leftJoin('devices', 'storage.device_id', 'devices.device_id')
            ->select('storage.device_id', 'storage_id', 'storage_descr', 'storage_perc', 'storage_perc_warn')
            ->where('devices.last_polled', '>', Carbon::now()->subMinutes($settings['time_interval']))
            ->when($settings['device_group'], function ($query) use ($settings) {
                $query->inDeviceGroup($settings['device_group']);
            })
            ->orderBy('storage_perc', $sort)
            ->limit($settings['device_count']);

        $results = $query->get()->map(function ($storage) {
            $device = $storage->device;

            $graph_array = [
                'height' => 100,
                'width' => 210,
                'to' => Carbon::now()->timestamp,
                'from' => Carbon::now()->subDay()->timestamp,
                'id' => $storage->storage_id,
                'type' => 'storage_usage',
                'legend' => 'no',
            ];
            $overlib_content = Url::overlibContent($graph_array, $device->displayName() . ' - ' . $storage->storage_descr);

            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = Url::generate($link_array);

            return [
                Url::deviceLink($device, $device->shortDisplayName()),
                StringHelpers::shortenText($storage->storage_descr, 50),
                Url::overlibLink(
                    $link,
                    Html::percentageBar(150, 20, $storage->storage_perc, '', $storage->storage_perc . '%', $storage->storage_perc_warn),
                    $overlib_content
                ),
            ];
        });

        return $this->formatData(['Storage Device', 'Disk usage'], $results);
    }
}
