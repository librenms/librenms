<?php

/**
 * OverviewController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Enum\Sensor;
use LibreNMS\Interfaces\Plugins\Hooks\DeviceOverviewHook;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Plugins;

class OverviewController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return true;
    }

    public function slug(): string
    {
        return 'overview';
    }

    public function icon(): string
    {
        return 'fa-lightbulb-o';
    }

    public function name(): string
    {
        return __('Overview');
    }

    public function data(Device $device, Request $request): array
    {
        $device->load([
            'applications.metrics',
            'attribs',
            'groups' => fn ($query) => $query->orderBy('name'),
            'location',
            'maps' => fn ($query) => $query->orderBy('name'),
            'mempools',
            'printerSupplies',
            'processors',
            'sensors',
            'services' => fn ($query) => $query->orderBy('service_type'),
            'storage' => fn ($query) => $query->orderBy('storage_descr'),
            'transceivers.port',
        ]);

        $device->loadCount([
            'ports as ports_total_count' => fn ($query) => $query->where('deleted', '!=', 1),
            'ports as ports_up_count' => fn ($query) => $query->where('deleted', '!=', 1)->where('disabled', 0)->where('ignore', 0)->where('ifOperStatus', 'up'),
            'ports as ports_down_count' => fn ($query) => $query->where('deleted', '!=', 1)->where('disabled', 0)->where('ignore', 0)->where('ifOperStatus', 'down'),
            'ports as ports_disabled_count' => fn ($query) => $query->where('deleted', '!=', 1)->where('disabled', 1),
        ]);

        $eventlogs = $device->eventlogs()->latest('datetime')->limit(10)->get();
        $eventPorts = Port::query()
            ->whereIn('port_id', $eventlogs->where('type', 'interface')->pluck('reference'))
            ->get()
            ->keyBy('port_id');

        $syslogs = LibrenmsConfig::get('enable_syslog')
            ? $device->syslogs()->latest('timestamp')->limit(20)->get()
            : collect();

        $activePorts = $device->ports()
            ->where('deleted', '!=', 1)
            ->where('disabled', 0)
            ->orderBy('ifName')
            ->get();

        return [
            'activePorts' => $activePorts,
            'eventlogs' => $eventlogs,
            'eventPorts' => $eventPorts,
            'graylog' => LibrenmsConfig::get('graylog.server') ? [
                'url' => route('table.graylog'),
                'rowCount' => LibrenmsConfig::get('graylog.device-page.rowCount', 10),
                'loglevel' => LibrenmsConfig::get('graylog.device-page.loglevel', 7),
            ] : null,
            'pingGraph' => $device->os === 'ping' && Rrd::checkRrdExists(Rrd::name($device->hostname, 'icmp-perf')),
            'pluginHtml' => Plugins::call('device_overview_container', [$device->toArray()]),
            'pluginViews' => \PluginManager::call(DeviceOverviewHook::class, ['device' => $device]),
            'puppetAgent' => $device->applications->firstWhere('app_type', 'puppet-agent'),
            'sensorGroups' => $this->sensorGroups($device),
            'syslogs' => $syslogs,
        ];
    }

    /**
     * @return Collection<string, array{
     *     sensor: Sensor,
     *     groups: Collection<int|string, Collection<int, \App\Models\Sensor>>
     * }>
     */
    private function sensorGroups(Device $device): Collection
    {
        return collect(Sensor::cases())
            ->mapWithKeys(function (Sensor $sensorClass) use ($device): array {
                $sensors = $device->sensors
                    ->where('sensor_class', $sensorClass->value)
                    ->where('group', '!=', 'transceiver')
                    ->sortBy([['group', 'asc'], ['sensor_descr', 'asc']]);

                return $sensors->isEmpty() ? [] : [
                    $sensorClass->value => [
                        'sensor' => $sensorClass,
                        'groups' => $sensors->toBase()->groupBy('group'),
                    ],
                ];
            });
    }
}
