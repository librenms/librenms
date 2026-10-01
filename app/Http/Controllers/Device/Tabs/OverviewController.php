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
use LibreNMS\Util\Rewrite;

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
            'ports as ports_total_count' => fn ($query) => $query->isNotDeleted(),
            'ports as ports_up_count' => fn ($query) => $query->isUp(),
            'ports as ports_down_count' => fn ($query) => $query->isDown(),
            'ports as ports_disabled_count' => fn ($query) => $query->isDisabled(),
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
     *     groups: Collection<int|string, Collection<int, array{sensor: \App\Models\Sensor, description: string, graphLink: string}>>
     * }>
     */
    private function sensorGroups(Device $device): Collection
    {
        $sensorOrder = [
            Sensor::Charge,
            Sensor::Temperature,
            Sensor::Humidity,
            Sensor::Fanspeed,
            Sensor::Dbm,
            Sensor::Voltage,
            Sensor::Current,
            Sensor::Runtime,
            Sensor::Power,
            Sensor::PowerConsumed,
            Sensor::PowerFactor,
            Sensor::Frequency,
            Sensor::Load,
            Sensor::State,
            Sensor::Count,
            Sensor::Percent,
            Sensor::Signal,
            Sensor::TvSignal,
            Sensor::Bitrate,
            Sensor::Airflow,
            Sensor::Snr,
            Sensor::Pressure,
            Sensor::Cooling,
            Sensor::Delay,
            Sensor::QualityFactor,
            Sensor::ChromaticDispersion,
            Sensor::Ber,
            Sensor::Eer,
            Sensor::Waterflow,
            Sensor::Loss,
            Sensor::SignalLoss,
        ];

        return collect($sensorOrder)
            ->mapWithKeys(function (Sensor $sensorClass) use ($device): array {
                $sensors = $device->sensors
                    ->where('sensor_class', $sensorClass->value)
                    ->where('group', '!=', 'transceiver')
                    ->sortBy([['group', 'asc'], ['sensor_descr', 'asc']]);

                $preparedSensors = $sensors
                    ->map(function (\App\Models\Sensor $sensor) use ($device, $sensorClass): array {
                        $description = $sensor->poller_type === 'ipmi'
                            ? Rewrite::ipmiSensorName($device->hardware, (string) $sensor->sensor_descr)
                            : (string) $sensor->sensor_descr;
                        $description = Rewrite::shortenIfName(substr($description, 0, 48));

                        return [
                            'sensor' => $sensor,
                            'description' => $description,
                            'graphLink' => route('graphs', [
                                'type' => 'sensor_' . $sensorClass->value,
                                'from' => LibrenmsConfig::get('time.day'),
                                'id' => $sensor->sensor_id,
                            ]),
                        ];
                    });

                return $sensors->isEmpty() ? [] : [
                    $sensorClass->value => [
                        'sensor' => $sensorClass,
                        'groups' => $preparedSensors->toBase()->groupBy(fn (array $data) => $data['sensor']->group),
                    ],
                ];
            });
    }
}
