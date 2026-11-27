<?php

/**
 * UpsMib.php
 *
 * Discover UPS sensors from UPS-MIB (RFC 1628)
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

use App\Facades\LibrenmsConfig;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Number;
use SnmpQuery;

trait UpsMib
{
    /**
     * UPS-MIB sensors of OS that have the rfc1628_compat setting
     *
     * @return Collection<int, Sensor>
     */
    public function discoverUpsMibSensors(): Collection
    {
        $sensors = new Collection;

        if (! LibrenmsConfig::getOsSetting($this->getName(), 'rfc1628_compat', false)) {
            return $sensors;
        }

        // UPS-MIB is small, walking all of it is cheaper than getting the values separately
        $data = SnmpQuery::walk('UPS-MIB::upsObjects');
        $scalar = fn (string $name) => $data->values()["UPS-MIB::$name.0"] ?? null;

        $this->upsMibSensor($sensors, SensorType::Temperature, '.1.3.6.1.2.1.33.1.2.7.0', 0, 'Battery', $scalar('upsBatteryTemperature'));
        $this->upsMibSensor($sensors, SensorType::Charge, '.1.3.6.1.2.1.33.1.2.4.0', 500, 'Battery charge remaining', $scalar('upsEstimatedChargeRemaining'), ['sensor_limit_low' => 15, 'sensor_limit_low_warn' => 50]);
        $this->upsMibSensor($sensors, SensorType::Count, '.1.3.6.1.2.1.33.1.6.1.0', 0, 'UPS Alarms', $scalar('upsAlarmsPresent'));
        $this->upsMibSensor($sensors, SensorType::Runtime, '.1.3.6.1.2.1.33.1.2.2.0', 100, 'Time on battery', $scalar('upsSecondsOnBattery'), [
            'sensor_limit_low' => 0, 'sensor_limit_low_warn' => 0, 'sensor_limit_warn' => 1, 'sensor_limit' => 2,
        ]);
        $this->upsMibSensor($sensors, SensorType::Runtime, '.1.3.6.1.2.1.33.1.2.3.0', 200, 'Estimated battery time remaining', $scalar('upsEstimatedMinutesRemaining'), [
            'sensor_limit_low' => 5, 'sensor_limit_low_warn' => 10, 'sensor_limit' => 10000,
        ]);
        $this->upsMibSensor($sensors, SensorType::Voltage, '.1.3.6.1.2.1.33.1.2.5.0', '1.2.5.0', 'Battery', $scalar('upsBatteryVoltage'));
        $this->upsMibSensor($sensors, SensorType::Current, '.1.3.6.1.2.1.33.1.2.6.0', 500, 'Battery', $scalar('upsBatteryCurrent'));
        $this->upsMibSensor($sensors, SensorType::Frequency, '.1.3.6.1.2.1.33.1.4.2.0', '4.2.0', 'Output', $scalar('upsOutputFrequency'));
        $this->upsMibSensor($sensors, SensorType::Frequency, '.1.3.6.1.2.1.33.1.5.1.0', '5.1.0', 'Bypass', $scalar('upsBypassFrequency'));

        foreach ([
            ['upsInputFrequency', SensorType::Frequency, '.1.3.6.1.2.1.33.1.3.3.1.2', 'Input', fn ($index) => "3.2.0.$index"],
            ['upsOutputCurrent', SensorType::Current, '.1.3.6.1.2.1.33.1.4.4.1.3', 'Output', fn ($index) => $index],
            ['upsInputCurrent', SensorType::Current, '.1.3.6.1.2.1.33.1.3.3.1.4', 'Input', fn ($index) => 100 + $index],
            ['upsBypassCurrent', SensorType::Current, '.1.3.6.1.2.1.33.1.5.3.1.3', 'Bypass', fn ($index) => 200 + $index],
            ['upsOutputPower', SensorType::Power, '.1.3.6.1.2.1.33.1.4.4.1.4', 'Output', fn ($index) => 300 + $index],
            ['upsInputTruePower', SensorType::Power, '.1.3.6.1.2.1.33.1.3.3.1.5', 'Input', fn ($index) => 100 + $index],
            ['upsBypassPower', SensorType::Power, '.1.3.6.1.2.1.33.1.5.3.1.4', 'Bypass', fn ($index) => 200 + $index],
            ['upsOutputVoltage', SensorType::Voltage, '.1.3.6.1.2.1.33.1.4.4.1.2', 'Output', fn ($index) => $index],
            ['upsInputVoltage', SensorType::Voltage, '.1.3.6.1.2.1.33.1.3.3.1.3', 'Input', fn ($index) => 100 + $index],
            ['upsBypassVoltage', SensorType::Voltage, '.1.3.6.1.2.1.33.1.5.3.1.2', 'Bypass', fn ($index) => 200 + $index],
            ['upsOutputPercentLoad', SensorType::Load, '.1.3.6.1.2.1.33.1.4.4.1.5', 'Percentage load', fn ($index) => 500 + $index],
        ] as [$name, $type, $oid, $descr, $sensorIndex]) {
            // some devices add .0 to the line index
            $lines = [];
            foreach ($data->values() as $key => $value) {
                if (preg_match("/^UPS-MIB::$name\\[(\\d+)](.*)$/", (string) $key, $matches)) {
                    $lines[$matches[1]] ??= [$matches[1] . str_replace(['][', '[', ']'], ['.', '.', ''], $matches[2]), $value];
                }
            }

            foreach ($lines as $line => [$index, $value]) {
                $lineDescr = count($lines) > 1 ? ($type === SensorType::Load ? "$descr $line" : "$descr Phase $line") : $descr;
                $this->upsMibSensor($sensors, $type, "$oid.$index", $sensorIndex($line), $lineDescr, $value);
            }
        }

        $this->upsMibState($sensors, 'upsBatteryStatusState', '.1.3.6.1.2.1.33.1.2.1.0', 'Battery Status', $scalar('upsBatteryStatus'), [
            ['value' => 1, 'generic' => 3, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 0, 'descr' => 'Normal'],
            ['value' => 3, 'generic' => 2, 'descr' => 'Low'],
            ['value' => 4, 'generic' => 2, 'descr' => 'Depleted'],
        ]);
        $this->upsMibState($sensors, 'upsOutputSourceState', '.1.3.6.1.2.1.33.1.4.1.0', 'Output Source', $scalar('upsOutputSource'), [
            ['value' => 1, 'generic' => 3, 'descr' => 'Other'],
            ['value' => 2, 'generic' => 3, 'descr' => 'None'],
            ['value' => 3, 'generic' => 0, 'descr' => 'Normal'],
            ['value' => 4, 'generic' => 1, 'descr' => 'Bypass'],
            ['value' => 5, 'generic' => 2, 'descr' => 'Battery'],
            ['value' => 6, 'generic' => 2, 'descr' => 'Booster'],
            ['value' => 7, 'generic' => 2, 'descr' => 'Reducer'],
        ]);
        $this->upsMibState($sensors, 'upsTestResult', '.1.3.6.1.2.1.33.1.7.3.0', 'UPS Test', $scalar('upsTestResultsSummary'), [
            ['value' => 1, 'generic' => 0, 'descr' => 'OK'],
            ['value' => 2, 'generic' => 1, 'descr' => 'Warning'],
            ['value' => 3, 'generic' => 2, 'descr' => 'Error'],
            ['value' => 4, 'generic' => 1, 'descr' => 'Aborted'],
            ['value' => 5, 'generic' => 1, 'descr' => 'inProgress'],
            ['value' => 6, 'generic' => 3, 'descr' => 'noTestInitiated'],
        ]);

        return $sensors;
    }

    /**
     * The divisor of UPS-MIB current, frequency, load, runtime and voltage values, some devices do not use the units of the MIB
     */
    protected function upsMibDivisor(SensorType $type, string $oid): int
    {
        if ($type === SensorType::Load) {
            return 1;
        }

        if ($type === SensorType::Voltage && ! str_starts_with($oid, '.1.3.6.1.2.1.33.1.2.5.')) {
            return 1;
        }

        if ($type === SensorType::Runtime) {
            // upsSecondsOnBattery is in seconds, upsEstimatedMinutesRemaining in minutes
            return str_starts_with($oid, '.1.3.6.1.2.1.33.1.2.2.') ? 60 : 1;
        }

        return 10;
    }

    /**
     * @param  Collection<int, Sensor>  $sensors
     * @param  array<string, int>  $limits
     */
    private function upsMibSensor(Collection $sensors, SensorType $type, string $oid, int|string $index, string $descr, mixed $value, array $limits = []): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $divisor = in_array($type, [SensorType::Current, SensorType::Frequency, SensorType::Load, SensorType::Runtime, SensorType::Voltage])
            ? $this->upsMibDivisor($type, $oid)
            : 1;
        $sensors->push(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => $type->value,
            'sensor_oid' => $oid,
            'sensor_index' => $index,
            'sensor_type' => 'rfc1628',
            'sensor_descr' => $descr,
            'sensor_divisor' => $divisor,
            'sensor_multiplier' => 1,
            'sensor_current' => Number::cast($value) / $divisor,
            'rrd_type' => 'GAUGE',
            ...$limits,
        ]));
    }

    /**
     * @param  Collection<int, Sensor>  $sensors
     * @param  list<array{value: int, generic: int, descr: string}>  $states
     */
    private function upsMibState(Collection $sensors, string $name, string $oid, string $descr, mixed $value, array $states): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $sensors->push(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => $oid,
            'sensor_index' => 0,
            'sensor_type' => $name,
            'sensor_descr' => $descr,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_current' => Number::cast($value),
            'entPhysicalIndex' => 0,
            'rrd_type' => 'GAUGE',
        ])->withStates($states));
    }
}
