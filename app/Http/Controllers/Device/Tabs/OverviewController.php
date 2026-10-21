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
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorCategory;
use LibreNMS\Enum\SensorType;
use LibreNMS\Interfaces\Plugins\Hooks\DeviceOverviewHook;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Plugins;
use LibreNMS\Util\Rewrite;

/**
 * @phpstan-type SensorRow array{sensor: Sensor, description: string, graphType: string, graphLink: string}
 */
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
            'sensors.translations',
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
            'sensorPanels' => $this->sensorPanels($device),
            'syslogs' => $syslogs,
        ];
    }

    /**
     * Sensor panels: one panel per sensor category, then one panel per sensor class for sensors without a category
     *
     * @return Collection<int, array{
     *     title: string,
     *     icon: string,
     *     href: string,
     *     groups: Collection<int|string, Collection<int, SensorRow>>
     * }>
     */
    private function sensorPanels(Device $device): Collection
    {
        $sensorOrder = [
            SensorType::Charge,
            SensorType::Temperature,
            SensorType::Humidity,
            SensorType::Fanspeed,
            SensorType::Dbm,
            SensorType::Voltage,
            SensorType::Current,
            SensorType::Runtime,
            SensorType::Power,
            SensorType::PowerConsumed,
            SensorType::PowerFactor,
            SensorType::Frequency,
            SensorType::Load,
            SensorType::State,
            SensorType::Count,
            SensorType::Percent,
            SensorType::Signal,
            SensorType::TvSignal,
            SensorType::Bitrate,
            SensorType::Airflow,
            SensorType::Snr,
            SensorType::Pressure,
            SensorType::Cooling,
            SensorType::Delay,
            SensorType::QualityFactor,
            SensorType::ChromaticDispersion,
            SensorType::Ber,
            SensorType::Eer,
            SensorType::Waterflow,
            SensorType::Loss,
            SensorType::SignalLoss,
        ];

        [$categorySensors, $classSensors] = $device->sensors
            ->where('group', '!=', 'transceiver')
            ->partition(fn (Sensor $sensor): bool => $sensor->category !== null);
        $categorySensors = $categorySensors->groupBy(fn (Sensor $sensor): string => $sensor->category->value);
        $classSensors = $classSensors->groupBy('sensor_class');

        $categoryPanels = collect(SensorCategory::cases())
            ->filter(fn (SensorCategory $category): bool => $categorySensors->has($category->value))
            ->map(function (SensorCategory $category) use ($device, $categorySensors): array {
                $sensors = $categorySensors->get($category->value);

                // a panel with a single sensor class links to that class on the health tab, like the class panels
                $classes = $sensors->pluck('sensor_class')->unique();
                $healthVars = $classes->count() === 1 ? ['vars' => 'metric=' . $classes->first()] : [];

                return [
                    'title' => $category->label(),
                    'icon' => 'fa fa-' . $category->icon(),
                    'href' => route('device', ['device' => $device->device_id, 'tab' => 'health'] + $healthVars),
                    'groups' => $this->groupSensors($device, $sensors),
                ];
            });

        $classPanels = collect($sensorOrder)
            ->filter(fn (SensorType $sensorClass): bool => $classSensors->has($sensorClass->value))
            ->map(fn (SensorType $sensorClass): array => [
                'title' => $sensorClass->label(),
                'icon' => 'fa fa-' . $sensorClass->icon(),
                'href' => route('device', ['device' => $device->device_id, 'tab' => 'health', 'vars' => 'metric=' . $sensorClass->value]),
                'groups' => $this->groupSensors($device, $classSensors->get($sensorClass->value)),
            ]);

        return $categoryPanels->values()->concat($classPanels);
    }

    /**
     * @param  Collection<int, Sensor>  $sensors
     * @return Collection<int|string, Collection<int, SensorRow>>
     */
    private function groupSensors(Device $device, Collection $sensors): Collection
    {
        $from = LibrenmsConfig::get('time.day');

        return $sensors
            ->sortBy([
                fn (Sensor $a, Sensor $b): int => strnatcasecmp((string) $a->group, (string) $b->group),
                fn (Sensor $a, Sensor $b): int => strnatcasecmp((string) $a->sensor_descr, (string) $b->sensor_descr),
            ])
            ->map(function (Sensor $sensor) use ($device, $from): array {
                $description = $sensor->poller_type === 'ipmi'
                    ? Rewrite::ipmiSensorName($device->hardware, (string) $sensor->sensor_descr)
                    : (string) $sensor->sensor_descr;
                $description = Rewrite::shortenIfName(substr($description, 0, 48));
                $graphType = $sensor->getGraphType();

                return [
                    'sensor' => $sensor,
                    'description' => $description,
                    'graphType' => $graphType,
                    'graphLink' => route('graphs', [
                        'type' => $graphType,
                        'from' => $from,
                        'id' => $sensor->sensor_id,
                    ]),
                ];
            })
            ->groupBy(fn (array $data) => $data['sensor']->group);
    }
}
