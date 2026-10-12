<?php

/**
 * Ipmi.php
 *
 * Discover and poll IPMI sensors via ipmitool
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Discovery\Sensor as SensorDiscovery;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

class Ipmi implements Module
{
    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->ipmiIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        $sensorDiscovery = new SensorDiscovery($device);

        if ($ipmi = Ipmitool::init($device)) {
            $sensor_values = $ipmi->sensors();
            usort($sensor_values, fn ($a, $b) => $a[0] <=> $b[0]);
            $index = 0;

            foreach ($sensor_values as [$descr, $current, $unit, $state, $low_nonrecoverable, $low_limit, $low_warn, $high_warn, $high_limit]) {
                if ($current == 'na' || ! LibrenmsConfig::has("ipmi_unit.$unit")) {
                    continue;
                }

                // units mapped to an empty class (discrete) still consume an index to keep existing sensor_index stable
                $sensor_index = $index++;
                $sensor_class = LibrenmsConfig::get("ipmi_unit.$unit");
                if (empty($sensor_class)) {
                    continue;
                }

                $sensorDiscovery->discover(new Sensor([
                    'device_id' => $device->device_id,
                    'poller_type' => 'ipmi',
                    'sensor_class' => $sensor_class,
                    'sensor_oid' => $descr,
                    'sensor_index' => $sensor_index,
                    'sensor_type' => 'ipmi',
                    'sensor_descr' => $descr,
                    'sensor_limit' => $high_limit == 'na' ? null : (float) $high_limit,
                    'sensor_limit_warn' => $high_warn == 'na' ? null : (float) $high_warn,
                    'sensor_limit_low' => $low_limit == 'na' ? null : (float) $low_limit,
                    'sensor_limit_low_warn' => $low_warn == 'na' ? null : (float) $low_warn,
                    'sensor_current' => Number::cast($current),
                    'rrd_type' => 'GAUGE',
                ]));
            }
        }

        $sensorDiscovery->sync(poller_type: 'ipmi');
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->ipmiIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $ipmiSensors = $device->sensors()->where('poller_type', 'ipmi')
            ->get()->groupBy('sensor_class')->map->keyBy('sensor_descr');

        if ($ipmiSensors->isEmpty()) {
            return;
        }

        $ipmi = Ipmitool::init($device);
        if ($ipmi === null) {
            return;
        }

        Log::info('Fetching IPMI sensor data...');
        foreach ($ipmi->sdr() as [$descr, $value, $unit]) {
            $descr = trim($descr, ' ');
            $ipmi_unit_type = LibrenmsConfig::get("ipmi_unit.$unit");

            /** @var Sensor|null $sensor */
            $sensor = $ipmiSensors->get($ipmi_unit_type)?->get($descr);
            if ($sensor === null) {
                continue;
            }

            // SDR records can include hexadecimal values, identified by an h like "93h"
            if (preg_match('/^([0-9A-Fa-f]+)h$/', $value, $matches)) {
                $value = hexdec($matches[1]);
            }
            $value = Number::cast($value);
            $sensor->sensor_current = $value;
            $sensor->save();

            Log::info("  $descr: $value $unit");

            $datastore->put($os->getDeviceArray(), 'ipmi', [
                'sensor_class' => $sensor->sensor_class,
                'sensor_type' => $sensor->sensor_type,
                'sensor_descr' => $sensor->sensor_descr,
                'sensor_index' => $sensor->sensor_index,
                'rrd_name' => ['sensor', ...array_values($sensor->labels())],
                'rrd_def' => RrdDefinition::make()->addDataset('sensor', 'GAUGE', -20000, 20000),
            ], [
                'sensor' => $value,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->sensors()->where('poller_type', 'ipmi')->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->sensors()->where('poller_type', 'ipmi')->delete();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dump(Device $device, string $type): ?array
    {
        return null; // IPMI sensors are included in the sensors module dump
    }
}
