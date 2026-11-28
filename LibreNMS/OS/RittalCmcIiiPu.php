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
use Illuminate\Support\Str;
use LibreNMS\Discovery\DiscoversSensors;
use LibreNMS\Enum\SensorType;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

class RittalCmcIiiPu extends OS
{
    /**
     * Sensor class by cmcIIIVarUnit, values without a unit are states
     */
    private const UNIT_TYPES = [
        '' => SensorType::State,
        'mA' => SensorType::Current,
        'A' => SensorType::Current,
        'Wh' => SensorType::PowerConsumed,
        'VAh' => SensorType::PowerConsumed,
        'kWh' => SensorType::PowerConsumed,
        'kVAh' => SensorType::PowerConsumed,
        'Hz' => SensorType::Frequency,
        'degree C' => SensorType::Temperature,
        'degree F' => SensorType::Temperature,
        '°C' => SensorType::Temperature,
        '°F' => SensorType::Temperature,
        'l/min' => SensorType::Waterflow,
        'V' => SensorType::Voltage,
        'W' => SensorType::Power,
        'VA' => SensorType::Power,
        'var' => SensorType::Power,
        '%' => SensorType::Percent,
    ];

    /**
     * ENTITY-SENSOR-MIB duplicates the CMC III sensors
     */
    public function discoverEntitySensors(): Collection
    {
        return new Collection;
    }

    /**
     * cmcIIIVarTable rows are named <sensor>.<variable> (Temperature.Value, Temperature.SetPtHighAlarm, ...)
     * and typed by cmcIIIVarType. Sensors are numbered in table order.
     *
     * @return Collection<int, Sensor>
     */
    #[DiscoversSensors(SensorType::Current, SensorType::Frequency, SensorType::Percent, SensorType::Power, SensorType::PowerConsumed, SensorType::State, SensorType::Temperature, SensorType::Voltage, SensorType::Waterflow)]
    public function discoverCmcIiiSensors(): Collection
    {
        $groups = SnmpQuery::enumStrings()->walk('RITTAL-CMC-III-MIB::cmcIIIVarTable')
            ->mapTable(function (array $entry, string $unit, string $var) {
                $name = str_replace('.', ' ', Str::beforeLast((string) ($entry['RITTAL-CMC-III-MIB::cmcIIIVarName'] ?? ''), '.'));

                return [
                    'sensor' => "$unit $name",
                    'name' => $name,
                    'oid' => ".1.3.6.1.4.1.2606.7.4.2.2.1.11.$unit.$var",
                    'type' => $entry['RITTAL-CMC-III-MIB::cmcIIIVarType'] ?? '',
                    'unit' => $entry['RITTAL-CMC-III-MIB::cmcIIIVarUnit'] ?? '',
                    'scale' => (int) ($entry['RITTAL-CMC-III-MIB::cmcIIIVarScale'] ?? 0),
                    'int' => $entry['RITTAL-CMC-III-MIB::cmcIIIVarValueInt'] ?? null,
                    'string' => (string) ($entry['RITTAL-CMC-III-MIB::cmcIIIVarValueStr'] ?? ''),
                ];
            })
            ->groupBy('sensor')
            ->values()
            ->map(fn (Collection $rows) => $rows->keyBy('type'));

        // the description row is the user set name, duplicates are numbered in table order
        $descriptions = $groups->map(function (Collection $variables) {
            $name = $variables->first()['name'];
            $descr = $variables['description']['string'] ?? $name;

            return str_contains($name, $descr) ? $descr : "$name $descr";
        });
        $counts = $descriptions->countBy();
        $descriptions = $descriptions->map(fn (string $descr, int $id) => $counts[$descr] > 1
            ? sprintf('%s %02d', $descr, $descriptions->take($id + 1)->countBy()[$descr])
            : $descr);

        // state names include the serial, the logic of a sensor depends on the configuration of the device
        $serial = (string) ($this->getDevice()->serial ?: SnmpQuery::get('RITTAL-CMC-III-MIB::cmcIIIUnitSerial.0')->value());

        // skip sensors without a value or with an unsupported unit (s, GB, ...),
        // filter keeps the keys, so skipped groups still count towards the index
        return $groups
            ->filter(fn (Collection $variables) => isset($variables['value'], self::UNIT_TYPES[$variables['value']['unit']])
                && ! in_array($variables->first()['name'], ['System V24 Port', 'Memory USB-Stick', 'Memory SD-Card', 'Login'])
                && ! preg_match('/(Power Factor)|(Runtime)/', $variables->first()['name']))
            ->map(fn (Collection $variables, int $id) => $this->makeCmcIiiSensor($variables, $id + 1, $descriptions[$id], $serial))
            ->values();
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $variables  rows of one sensor keyed by variable type
     */
    private function makeCmcIiiSensor(Collection $variables, int $index, string $descr, string $serial): Sensor
    {
        $value = $variables['value'];
        $name = $variables->first()['name'];

        $type = self::UNIT_TYPES[$value['unit']];

        // a negative scale divides, a positive one multiplies, mA and Wh have a scale of 1
        $divisor = in_array($value['unit'], ['mA', 'Wh', 'VAh']) ? 1000 : max(1, -$value['scale']);
        $multiplier = max(1, $value['scale']);
        $userFunc = in_array($value['unit'], ['degree F', '°F']) ? 'fahrenheit_to_celsius' : null;

        // the same conversion as polling, limits are raw values too
        $convert = function ($raw) use ($divisor, $multiplier, $userFunc) {
            $converted = $raw / $divisor * $multiplier;

            return $userFunc ? UserFuncHelper::apply($userFunc, $converted) : $converted;
        };
        $limit = fn (string $varType) => isset($variables[$varType]['int']) ? $convert($variables[$varType]['int']) : null;

        // logic looks like "0:Off / 1:On", there is none for Smoke and Access sensors
        $logic = match ($name) {
            'Smoke' => ['OK', 'Alarm'],
            'Access' => ['Closed', 'Open'],
            default => isset($variables['logic']) ? array_map(fn (string $state) => substr($state, 2), explode(' / ', $variables['logic']['string']) + ['', '']) : null,
        };

        $sensor = new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => $type->value,
            'sensor_oid' => $value['oid'],
            'sensor_index' => $index,
            'sensor_type' => $logic ? "{$name}_$serial" : $name,
            'sensor_descr' => $descr,
            'sensor_divisor' => $divisor,
            'sensor_multiplier' => $multiplier,
            'user_func' => $userFunc,
            'sensor_current' => $convert(Number::cast($value['int'] ?: $value['string'])),
            'sensor_limit_low' => $limit('setLow'),
            'sensor_limit_low_warn' => $limit('setWarnLow'),
            'sensor_limit_warn' => $limit('setWarn'),
            'sensor_limit' => $limit('setHigh'),
            'rrd_type' => 'GAUGE',
        ]);

        return $logic ? $sensor->withStates([
            ['value' => 0, 'generic' => 0, 'descr' => $logic[0]],
            ['value' => 1, 'generic' => $name == 'Smoke' ? 2 : 0, 'descr' => $logic[1]],
        ]) : $sensor;
    }
}
