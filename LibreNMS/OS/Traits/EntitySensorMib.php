<?php

/**
 * EntitySensorMib.php
 *
 * Discover sensors from ENTITY-SENSOR-MIB
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Enum\SensorType;
use LibreNMS\Exceptions\SnmpConnectionException;
use LibreNMS\Util\Rewrite;
use SnmpQuery;

/**
 * @phpstan-type EntityNames array<int|string, array{entPhysicalDescr?: string, entPhysicalName?: string}>
 * @phpstan-type EntitySensorEntry array{entPhySensorType?: string, entPhySensorScale?: string, entPhySensorPrecision?: int|string, entPhySensorValue?: int|string, entPhySensorOperStatus?: string}
 */
trait EntitySensorMib
{
    /**
     * @return Collection<int, Sensor>
     *
     * @throws SnmpConnectionException when the device did not answer
     */
    public function discoverEntitySensors(): Collection
    {
        $sensors = new Collection;

        /** @var EntityNames $entities */
        $entities = SnmpQuery::hideMib()->abortOnFailure()->walk([
            'ENTITY-MIB::entPhysicalDescr',
            'ENTITY-MIB::entPhysicalName',
        ])->throwIfConnectionError()->table(1);

        if (empty($entities)) {
            return $sensors;
        }

        $data = SnmpQuery::hideMib()->enumStrings()->walk([
            'ENTITY-SENSOR-MIB::entPhySensorType',
            'ENTITY-SENSOR-MIB::entPhySensorScale',
            'ENTITY-SENSOR-MIB::entPhySensorPrecision',
            'ENTITY-SENSOR-MIB::entPhySensorValue',
            'ENTITY-SENSOR-MIB::entPhySensorOperStatus',
        ])->throwIfConnectionError()->table(1);

        foreach ($data as $index => $entry) {
            if (! is_array($entry)) {
                continue; // agents without index
            }

            $class = $this->entitySensorClass($entry, $entities[$index]['entPhysicalName'] ?? '');
            if ($class === null || ! is_numeric($index) || ! is_numeric($entry['entPhySensorValue'] ?? null)) {
                continue;
            }

            $descr = Rewrite::entityDescr($this->entitySensorDescr((int) $index, $entities));
            $scale = self::entitySensorScale($entry['entPhySensorScale'] ?? null, $entry['entPhySensorPrecision'] ?? null);
            if ($scale === null) {
                continue;
            }

            [$divisor, $multiplier] = $scale;
            $current = $entry['entPhySensorValue'] * $multiplier / $divisor;

            if ($class === SensorType::Temperature) {
                if ($current > 200) {
                    continue;
                }
                $descr = preg_replace('/[T|t]emperature[|s]/', '', $descr);
            }

            if (! $this->isValidEntitySensor($descr, $current, $entry)) {
                continue;
            }

            $sensor = $this->entitySensor(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => $class->value,
                'sensor_oid' => ".1.3.6.1.2.1.99.1.1.1.4.$index",
                'sensor_index' => $index,
                'sensor_type' => 'entity-sensor',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_current' => $current,
                'entPhysicalIndex' => $index,
                'rrd_type' => 'GAUGE',
            ]), $entry);

            if ($sensor !== null) {
                $sensor->sensor_descr = trim((string) $sensor->sensor_descr);
                $sensors->push($sensor);
            }
        }

        return $sensors;
    }

    /**
     * The description of an ENTITY-SENSOR-MIB sensor, before it is shortened with Rewrite::entityDescr()
     *
     * @param  EntityNames  $entities
     */
    protected function entitySensorDescr(int $index, array $entities): string
    {
        return ucwords($entities[$index]['entPhysicalName'] ?? '');
    }

    /**
     * Adjust an ENTITY-SENSOR-MIB sensor of this OS, null to skip it
     *
     * @param  EntitySensorEntry  $entry  the ENTITY-SENSOR-MIB values of the sensor
     */
    protected function entitySensor(Sensor $sensor, array $entry): ?Sensor
    {
        return $sensor;
    }

    /**
     * The divisor and multiplier for an EntitySensorDataScale and EntitySensorPrecision,
     * null when the database cannot store them (the divisor is a bigint, the multiplier an int)
     *
     * @return array{int, int}|null
     */
    public static function entitySensorScale(?string $scale, int|string|null $precision): ?array
    {
        $exponent = match ($scale) {
            'yocto' => -24,
            'zepto' => -21,
            'atto' => -18,
            'femto' => -15,
            'pico' => -12,
            'nano' => -9,
            'micro' => -6,
            'milli' => -3,
            'kilo' => 3,
            'mega' => 6,
            'giga' => 9,
            'tera' => 12,
            'exa' => 18,
            'peta' => 15,
            'zetta' => 21,
            'yotta' => 24,
            default => 0,
        };
        $divisorExponent = max(0, -$exponent) + (is_numeric($precision) && $precision > 0 ? (int) $precision : 0);
        $multiplierExponent = max(0, $exponent);

        // the same ratio with smaller factors
        $common = min($divisorExponent, $multiplierExponent);
        $divisorExponent -= $common;
        $multiplierExponent -= $common;

        if ($divisorExponent > 18 || $multiplierExponent > 9) {
            return null;
        }

        return [10 ** $divisorExponent, 10 ** $multiplierExponent];
    }

    /**
     * @param  EntitySensorEntry  $entry
     */
    private function entitySensorClass(array $entry, string $name): ?SensorType
    {
        return match ($entry['entPhySensorType'] ?? null) {
            'voltsDC', 'voltsAC' => SensorType::Voltage,
            'amperes' => SensorType::Current,
            'watts' => SensorType::Power,
            'hertz' => SensorType::Frequency,
            'percentRH' => SensorType::Humidity,
            'rpm' => SensorType::Fanspeed,
            'celsius' => SensorType::Temperature,
            'dBm' => SensorType::Dbm,
            // Cisco ASR920 reports optical power as other
            'other' => Str::contains($name, ['Rx Power Sensor', 'Tx Power Sensor']) ? SensorType::Dbm : null,
            default => null,
        };
    }

    /**
     * @param  EntitySensorEntry  $entry
     */
    private function isValidEntitySensor(string $descr, int|float $current, array $entry): bool
    {
        $regexes = array_merge(LibrenmsConfig::get('bad_entity_sensor_regex', []), LibrenmsConfig::getOsSetting($this->getName(), 'bad_entity_sensor_regex', []));
        foreach ($regexes as $regex) {
            if (preg_match($regex . 'i', $descr)) {
                Log::debug("Ignored entity sensor: $regex : $descr");

                return false;
            }
        }

        return $current != -127
            && ! in_array($entry['entPhySensorOperStatus'] ?? null, ['unavailable', 'nonoperational'])
            && $entry['entPhySensorValue'] != -1000000000;
    }
}
