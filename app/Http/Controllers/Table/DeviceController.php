<?php
/**
 * DeviceController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

class DeviceController extends TableController
{
    private $detailed; // display format is detailed

    protected function rules()
    {
        return [
            'format' => 'nullable|in:list_basic,list_detail',
            'os' => 'nullable|string',
            'version' => 'nullable|string',
            'hardware' => 'nullable|string',
            'features' => 'nullable|string',
            'location' => 'nullable|string',
            'type' => 'nullable|string',
            'state' => 'nullable|in:0,1,up,down',
            'disabled' => 'nullable|in:0,1',
            'ignore' => 'nullable|in:0,1',
            'disable_notify' => 'nullable|in:0,1',
            'group' => 'nullable|int',
            'poller_group' => 'nullable|int',
            'device_id' => 'nullable|int',
        ];
    }

    protected function filterFields($request)
    {
        return ['os', 'version', 'hardware', 'features', 'type', 'status' => 'state', 'disabled', 'disable_notify', 'ignore', 'location_id' => 'location', 'device_id' => 'device_id'];
    }

    protected function searchFields($request)
    {
        return ['sysName', 'hostname', 'hardware', 'os', 'locations.location'];
    }

    protected function sortFields($request)
    {
        return [
            'status' => 'status',
            'icon' => 'icon',
            'hostname' => 'hostname',
            'hardware' => 'hardware',
            'os' => 'os',
            'uptime' => \DB::raw('IF(`status` = 1, `uptime`, `last_polled` - NOW())'),
            'location' => 'location',
            'device_id' => 'device_id',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        /** @var Builder $query */
        $query = Device::hasAccess($request->user())->with('location')->withCount(['ports', 'sensors', 'wirelessSensors']);

        // if searching or sorting the location field, join the locations table
        if ($request->get('searchPhrase') || in_array('location', array_keys($request->get('sort', [])))) {
            $query->leftJoin('locations', 'locations.id', 'devices.location_id');
        }

        // filter device group, not sure this is the most efficient query
        if ($group = $request->get('group')) {
            $query->whereHas('groups', function ($query) use ($group) {
                $query->where('id', $group);
            });
        }

        if ($request->get('poller_group') !== null) {
            $query->where('poller_group', $request->get('poller_group'));
        }

        return $query;
    }

    protected function adjustFilterValue($field, $value)
    {
        if ($field == 'location' && ! is_numeric($value)) {
            return Location::query()->where('location', $value)->value('id');
        }

        if ($field == 'state' && ! is_numeric($value)) {
            return str_replace(['up', 'down'], [1, 0], $value);
        }

        return $value;
    }

    private function isDetailed()
    {
        if (is_null($this->detailed)) {
            $this->detailed = \Request::get('format', 'list_detail') == 'list_detail';
        }

        return $this->detailed;
    }

    /**
     * @param  Device  $device
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function formatItem($device)
    {
        return [
            'extra' => $this->getLabel($device),
            'status' => $this->getStatus($device),
            'maintenance' => $device->isUnderMaintenance(),
            'icon' => '<img src="' . asset($device->icon) . '" title="' . pathinfo($device->icon, PATHINFO_FILENAME) . '">',
            'hostname' => $this->getHostname($device),
            'metrics' => $this->getMetrics($device),
            'hardware' => Rewrite::ciscoHardware($device),
            'os' => $this->getOsText($device),
            'uptime' => (! $device->status && ! $device->last_polled) ? __('Never polled') : Time::formatInterval($device->status ? $device->uptime : $device->last_polled->diffInSeconds(), 'short'),
            'location' => $this->getLocation($device),
            'actions' => view('device.actions', ['actions' => $this->getActions($device)])->__toString(),
            'device_id' => $device->device_id,
        ];
    }

    /**
     * Get the device up/down status
     *
     * @param  Device  $device
     * @return string
     */
    private function getStatus($device)
    {
        if ($device->disabled == 1) {
            return 'disabled';
        } elseif ($device->status == 0) {
            return 'down';
        }

        return 'up';
    }

    /**
     * Get the status label class
     *
     * @param  Device  $device
     * @return string
     */
    private function getLabel($device)
    {
        if ($device->disabled == 1) {
            return 'blackbg';
        } elseif ($device->disable_notify == 1) {
            return 'blackbg';
        } elseif ($device->ignore == 1) {
            return 'label-default';
        } elseif ($device->status == 0) {
            return 'label-danger';
        } else {
            $warning_time = \LibreNMS\Config::get('uptime_warning', 84600);
            if ($device->uptime < $warning_time && $device->uptime != 0) {
                return 'label-warning';
            }

            return 'label-success';
        }
    }

    /**
     * @param  Device  $device
     * @return string
     */
    private function getHostname($device)
    {
        return (string) view('device.list.hostname', [
            'device' => $device,
            'detailed' => $this->isDetailed(),
        ]);
    }

    /**
     * @param  Device  $device
     * @return string
     */
    private function getOsText($device)
    {
        $os_text = Config::getOsSetting($device->os, 'text');

        if ($this->isDetailed()) {
            $os_text .= '<br />' . $device->version . ($device->features ? " ($device->features)" : '');
        }

        return $os_text;
    }

    /**
     * @param  Device  $device
     * @return string
     */
    private function getMetrics($device)
    {
        $port_count = $device->ports_count;
        $sensor_count = $device->sensors_count;
        $wireless_count = $device->wirelessSensors_count;

        $metrics = [];
        if ($port_count) {
            $metrics[] = $this->formatMetric($device, $port_count, 'ports', 'fa-link');
        }

        if ($sensor_count) {
            $metrics[] = $this->formatMetric($device, $sensor_count, 'health', 'fa-dashboard');
        }

        if ($wireless_count) {
            $metrics[] = $this->formatMetric($device, $wireless_count, 'wireless', 'fa-wifi');
        }

        $glue = $this->isDetailed() ? '<br />' : ' ';
        $metrics_content = implode(count($metrics) == 2 ? $glue : '', $metrics);

        return '<div class="device-table-metrics">' . $metrics_content . '</div>';
    }

    /**
     * @param  int|Device  $device
     * @param  mixed  $count
     * @param  mixed  $tab
     * @param  mixed  $icon
     * @return string
     */
    private function formatMetric($device, $count, $tab, $icon)
    {
        $html = '<a href="' . Url::deviceUrl($device, ['tab' => $tab]) . '">';
        $html .= '<span><i title="' . $tab . '" class="fa ' . $icon . ' fa-lg icon-theme"></i> ' . $count;
        $html .= '</span></a> ';

        return $html;
    }

    /**
     * @param  Device  $device
     * @return string
     */
    private function getLocation($device)
    {
        return extension_loaded('mbstring')
            ? mb_substr($device->location, 0, 32, 'utf8')
            : substr($device->location, 0, 32);
    }

    private function getActions(Device $device): array
    {
        $actions = [
            [
                [
                    'title' => 'View Device',
                    'href' => Url::deviceUrl($device),
                    'icon' => 'fa-id-card',
                    'external' => false,
                ],
                [
                    'title' => 'View alerts',
                    'href' => Url::deviceUrl($device, ['tab' => 'alerts']),
                    'icon' => 'fa-exclamation-circle',
                    'external' => false,
                ],
            ],
        ];

        if (\Auth::user()->hasGlobalAdmin()) {
            $actions[0][] = [
                'title' => 'Edit device',
                'href' => Url::deviceUrl($device, ['tab' => 'edit']),
                'icon' => 'fa-gear',
                'external' => false,
            ];
        }
        $row = $this->isDetailed() ? 1 : 0;

        $actions[$row][] = [
            'title' => 'Telnet to ' . $device->hostname,
            'href' => 'telnet://' . $device->hostname,
            'icon' => 'fa-terminal',
        ];

        $ssh_href = 'ssh://' . $device->hostname;
        if ($server = Config::get('gateone.server')) {
            $ssh_href = Config::get('gateone.use_librenms_user')
                ? $server . '?ssh=ssh://' . Auth::user()->username . '@' . $device->hostname . '&location=' . $device->hostname
                : $server . '?ssh=ssh://' . $device->hostname . '&location=' . $device->hostname;
        }

        $actions[$row][] = [
            'title' => 'SSH to ' . $device->hostname,
            'href' => $ssh_href,
            'icon' => 'fa-lock',
        ];

        $actions[$row][] = [
            'title' => 'Launch browser to ' . $device->hostname,
            'href' => 'https://' . $device->hostname,
            'onclick' => 'http_fallback(this); return false;',
            'icon' => 'fa-globe',
        ];

        foreach (array_values(Arr::wrap(Config::get('html.device.links'))) as $index => $custom) {
            if ($custom['action'] ?? false) {
                $row = $this->isDetailed() ? $index % 2 : 0;
                $custom['href'] = view(['template' => $custom['url']], ['device' => $device])->__toString(); // @phpstan-ignore-line
                $actions[$row][] = $custom;
            }
        }

        return $actions;
    }
}
