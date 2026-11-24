<?php

/**
 * NetSnmpSensors.php
 *
 * Sensors of the Net-SNMP agent: LM-SENSORS-MIB and the ups-nut extend
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

namespace LibreNMS\OS\Traits;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorType;
use SnmpQuery;

trait NetSnmpSensors
{
    /**
     * @return Collection<int, Sensor>
     */
    public function discoverNetSnmpSensors(): Collection
    {
        return $this->discoverLmSensors()->merge($this->discoverUpsNutSensors());
    }

    /**
     * LM-SENSORS-MIB temperature, fan and voltage tables
     *
     * @return Collection<int, Sensor>
     */
    public function discoverLmSensors(): Collection
    {
        $sensors = new Collection;
        $data = SnmpQuery::cache()->hideMib()->walk('LM-SENSORS-MIB::lmSensors')->table(1);

        foreach ([
            ['lmTempSensors', SensorType::Temperature, '.1.3.6.1.4.1.2021.13.16.2.1.3', 1000],
            ['lmFanSensors', SensorType::Fanspeed, '.1.3.6.1.4.1.2021.13.16.3.1.3', 1],
            ['lmVoltSensors', SensorType::Voltage, '.1.3.6.1.4.1.2021.13.16.4.1.3', 1000],
        ] as [$table, $type, $oid, $divisor]) {
            foreach ($data as $row) {
                $index = $row[$table . 'Index'] ?? null;
                if ($index === null || empty($row[$table . 'Device'])) {
                    continue;
                }

                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $type->value,
                    'sensor_oid' => "$oid.$index",
                    'sensor_index' => $index,
                    'sensor_type' => 'lmsensors',
                    'sensor_descr' => $row[$table . 'Device'],
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_current' => (int) ($row[$table . 'Value'] ?? 0) / $divisor,
                    'group' => 'lmsensors',
                    'rrd_type' => 'GAUGE',
                ]));
            }
        }

        return $sensors;
    }

    /**
     * The ups-nut extend outputs one value per line, the line number tells what it is
     *
     * @return Collection<int, Sensor>
     */
    public function discoverUpsNutSensors(): Collection
    {
        $sensors = new Collection;
        $lines = SnmpQuery::cache()->hideMib()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."ups-nut"')->table(3);
        $lines = array_map(fn ($line) => $line['nsExtendOutLine'] ?? null, array_shift($lines) ?? []);
        $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116';

        foreach ([
            // line => class, descr, divisor, limits (low, low warn, warn, high)
            1 => [SensorType::Charge, 'Battery Charge', 1, [0, 10, null, null]],
            3 => [SensorType::Runtime, 'Time Remaining', 60, [0, 0, null, 1000]],
            4 => [SensorType::Voltage, 'Battery Voltage', 1, [0, 0, null, 60]],
            5 => [SensorType::Voltage, 'Battery Nominal', 1, [0, 0, null, 60]],
            6 => [SensorType::Voltage, 'Line Nominal', 1, [null, null, null, null]],
            7 => [SensorType::Voltage, 'Input Voltage', 1, [200, 0, null, 280]],
            8 => [SensorType::Load, 'Ups Load', 1, [0, 0, null, 100]],
            24 => [SensorType::Temperature, 'Battery Temperature', 1, [10, 15, 35, 40]],
        ] as $line => [$type, $descr, $divisor, $limits]) {
            $value = $lines[$line] ?? null;
            // voltages may be 0, other values are integers that must not be 0
            $value = $type === SensorType::Voltage ? (is_numeric($value) ? $value : null) : ((int) $value ?: null);

            if ($value !== null) {
                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $type->value,
                    'sensor_oid' => "$oid.$line",
                    'sensor_index' => $line,
                    'sensor_type' => 'ups-nut',
                    'sensor_descr' => $descr,
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $value / $divisor,
                    'sensor_limit_low' => $limits[0],
                    'sensor_limit_low_warn' => $limits[1],
                    'sensor_limit_warn' => $limits[2],
                    'sensor_limit' => $limits[3],
                    'group' => 'ups-nut',
                    'rrd_type' => 'GAUGE',
                ]));
            }
        }

        foreach ([
            // state name, generic when true, generic when false, descr
            ['UPSOnLine', 0, 1, 'UPS on line'],
            ['UPSOnBattery', 1, 0, 'UPS on battery'],
            ['UPSLowBattery', 2, 0, 'UPS low battery'],
            ['UPSHighBattery', 1, 0, 'UPS high battery'],
            ['UPSBatteryReplace', 1, 0, 'UPS the battery needs to be replaced'],
            ['UPSBatteryCharging', 1, 0, 'UPS the battery is charging'],
            ['UPSBatteryDischarging', 1, 0, 'UPS the battery is discharging'],
            ['UPSUPSBypass', 1, 0, 'UPS bypass circuit is active'],
            ['UPSRuntimeCalibration', 1, 0, 'UPS is currently performing runtime calibration'],
            ['UPSOffline', 2, 0, 'UPS is offline and is not supplying power to the load'],
            ['UPSUPSOverloaded', 2, 0, 'UPS is overloaded.'],
            ['UPSUPSBuck', 1, 0, 'UPS is trimming incoming voltage'],
            ['UPSUPSBoost', 1, 0, 'UPS is boosting incoming voltage'],
            ['UPSForcedShutdown', 2, 0, 'UPS forced shutdown'],
        ] as $position => [$name, $true, $false, $descr]) {
            $line = 9 + $position;
            if (! isset($lines[$line])) {
                continue;
            }

            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => "$oid.$line",
                'sensor_index' => $line,
                'sensor_type' => $name,
                'sensor_descr' => $descr,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $lines[$line],
                'group' => 'ups-nut',
                'rrd_type' => 'GAUGE',
            ])->withStates([
                ['value' => 0, 'generic' => $false, 'descr' => 'False'],
                ['value' => 1, 'generic' => $true, 'descr' => 'True'],
            ]));
        }

        return $sensors;
    }
}
