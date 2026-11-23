<?php

/**
 * RittalCmcIiiPu.php
 *
 * Rittal CMC III PU
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

namespace LibreNMS\OS;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\SensorType;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

/**
 * cmcIIIVarTable has a row for each variable of a sensor (value, limits, logic).
 * The variables of a sensor are consecutive rows that share the name, without the last part.
 *
 * @phpstan-type CmcSensor array{name: string, descr: string, oid?: string, value?: int|float, type?: SensorType, divisor?: string, multiplier?: string, logic?: array{string, string}, user_func?: string, limits: array<string, mixed>}
 */
class RittalCmcIiiPu extends OS
{
    /**
     * ENTITY-SENSOR-MIB duplicates the CMC III sensors
     */
    public function discoverEntitySensors(): Collection
    {
        return new Collection;
    }

    protected function discoverOsSensors(): Collection
    {
        return $this->discoverCmcIiiSensors()->merge(parent::discoverOsSensors());
    }

    /**
     * @return Collection<int, Sensor>
     */
    public function discoverCmcIiiSensors(): Collection
    {
        $variables = SnmpQuery::hideMib()->enumStrings()->walk('RITTAL-CMC-III-MIB::cmcIIIVarTable')->valuesByIndex();
        /** @var array<int, CmcSensor> $cmcSensors */
        $cmcSensors = [];
        $descriptions = [];
        $lastPrefix = $prefix = '';

        foreach ($variables as $index => $entry) {
            $nameParts = explode('.', (string) ($entry['cmcIIIVarName'] ?? ''));
            array_pop($nameParts);
            $name = implode(' ', $nameParts);
            $id = count($cmcSensors);
            $indexParts = explode('.', (string) $index);
            if (count($indexParts) > 1) {
                $prefix = $indexParts[0];
            }

            // a new sensor starts when the name or the first index changes
            if (($cmcSensors[$id]['name'] ?? null) !== $name || $lastPrefix != $prefix) {
                $id = $id == 0 ? 1 : $id + 1;
                $valueString = (string) ($entry['cmcIIIVarValueStr'] ?? '');
                $descr = str_contains($name, $valueString) ? $valueString : "$name $valueString";
                $cmcSensors[$id] = ['name' => $name, 'descr' => $descr, 'limits' => []];
                $descriptions[$descr][] = $id;
                $lastPrefix = $prefix;
            }

            $this->addCmcIiiVariable($cmcSensors[$id], (string) $index, $entry);
        }

        // make descriptions unique
        foreach ($descriptions as $ids) {
            if (count($ids) > 1) {
                foreach ($ids as $position => $id) {
                    $cmcSensors[$id]['descr'] .= ' ' . str_pad((string) ($position + 1), 2, '0', STR_PAD_LEFT);
                }
            }
        }

        // state names include the serial, the logic of a sensor depends on the configuration of the device
        $serial = $this->getDevice()->serial ?: SnmpQuery::get('RITTAL-CMC-III-MIB::cmcIIIUnitSerial.0')->value();
        $sensors = new Collection;

        foreach ($cmcSensors as $id => $cmcSensor) {
            if (! isset($cmcSensor['oid'], $cmcSensor['type'])
                || in_array($cmcSensor['name'], ['System V24 Port', 'Memory USB-Stick', 'Memory SD-Card', 'Login'])
                || preg_match('/(Power Factor)|(Runtime)/', $cmcSensor['name'])) {
                Log::debug("{$cmcSensor['name']} skipped");
                continue;
            }

            // there is no logic for Smoke and Access sensors
            $logic = match ($cmcSensor['name']) {
                'Smoke' => ['OK', 'Alarm'],
                'Access' => ['Closed', 'Open'],
                default => $cmcSensor['logic'] ?? null,
            };

            // the same conversion as polling
            $scale = function ($value) use ($cmcSensor) {
                $value = $value / ($cmcSensor['divisor'] ?? 1) * ($cmcSensor['multiplier'] ?? 1);

                return isset($cmcSensor['user_func']) ? UserFuncHelper::apply($cmcSensor['user_func'], $value) : $value;
            };
            $limit = fn (string $name) => isset($cmcSensor['limits'][$name]) ? $scale($cmcSensor['limits'][$name]) : null;

            $sensor = new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => $cmcSensor['type']->value,
                'sensor_oid' => $cmcSensor['oid'],
                'sensor_index' => $id,
                'sensor_type' => $logic ? "{$cmcSensor['name']}_$serial" : $cmcSensor['name'],
                'sensor_descr' => $cmcSensor['descr'],
                'sensor_divisor' => $cmcSensor['divisor'] ?? 1,
                'sensor_multiplier' => $cmcSensor['multiplier'] ?? 1,
                'user_func' => $cmcSensor['user_func'] ?? null,
                'sensor_current' => $scale($cmcSensor['value'] ?? 0),
                'sensor_limit_low' => $limit('setLow'),
                'sensor_limit_low_warn' => $limit('setWarnLow'),
                'sensor_limit_warn' => $limit('setWarn'),
                'sensor_limit' => $limit('setHigh'),
                'rrd_type' => 'GAUGE',
            ]);

            $sensors->push($logic ? $sensor->withStates([
                ['value' => 0, 'generic' => 0, 'descr' => $logic[0]],
                ['value' => 1, 'generic' => $cmcSensor['name'] == 'Smoke' ? 2 : 0, 'descr' => $logic[1]],
            ]) : $sensor);
        }

        return $sensors;
    }

    /**
     * @param  CmcSensor  $sensor
     * @param  array<string, mixed>  $entry
     */
    private function addCmcIiiVariable(array &$sensor, string $index, array $entry): void
    {
        $type = (string) ($entry['cmcIIIVarType'] ?? '');

        if (in_array($type, ['setHigh', 'setWarn', 'setWarnLow', 'setLow'])) {
            $sensor['limits'][$type] = $entry['cmcIIIVarValueInt'] ?? null;
        } elseif ($type == 'logic') {
            $logic = explode(' / ', (string) ($entry['cmcIIIVarValueStr'] ?? ''));
            $sensor['logic'] = [substr($logic[0], 2), substr($logic[1] ?? '', 2)];
        } elseif ($type == 'value') {
            $sensor['oid'] = ".1.3.6.1.4.1.2606.7.4.2.2.1.11.$index";
            $sensor['value'] = Number::cast(empty($entry['cmcIIIVarValueInt']) ? ($entry['cmcIIIVarValueStr'] ?? '') : $entry['cmcIIIVarValueInt']);

            $scale = (string) ($entry['cmcIIIVarScale'] ?? '');
            if (str_starts_with($scale, '-')) {
                $sensor['divisor'] = substr($scale, 1);
            } elseif (str_starts_with($scale, '+')) {
                $sensor['multiplier'] = substr($scale, 1);
            }

            [$sensor['type'], $divisor] = match ((string) ($entry['cmcIIIVarUnit'] ?? '')) {
                'mA' => [SensorType::Current, '1000'], // the scale is 1 for mA values
                'A' => [SensorType::Current, null],
                'Wh', 'VAh' => [SensorType::PowerConsumed, '1000'],
                'kWh', 'kVAh' => [SensorType::PowerConsumed, null],
                'Hz' => [SensorType::Frequency, null],
                'degree C', 'degree F', '°C', '°F' => [SensorType::Temperature, null],
                'l/min' => [SensorType::Waterflow, null],
                'V' => [SensorType::Voltage, null],
                'W', 'VA', 'var' => [SensorType::Power, null],
                '%' => [SensorType::Percent, null],
                default => [SensorType::State, null],
            };

            if ($divisor !== null) {
                $sensor['divisor'] = $divisor;
            }

            if (in_array($entry['cmcIIIVarUnit'] ?? null, ['degree F', '°F'])) {
                $sensor['user_func'] = 'fahrenheit_to_celsius';
            }
        }
    }
}
