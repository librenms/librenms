<?php

/**
 * CiscoEntitySensorMib.php
 *
 * Discover sensors from CISCO-ENTITY-SENSOR-MIB
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

use App\Facades\PortCache;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Rewrite;
use SnmpQuery;

/**
 * @phpstan-type CiscoEntities array<int|string, array<int|string, mixed>>
 * @phpstan-type CiscoThreshold array{'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdSeverity'?: string, 'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation'?: string, 'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdValue'?: int|string}
 */
trait CiscoEntitySensorMib
{
    /**
     * @return array{int, int}
     */
    abstract public static function entitySensorScale(?string $scale, int|string|null $precision): ?array;

    /**
     * CISCO-ENTITY-SENSOR-MIB sensors replace the ENTITY-SENSOR-MIB sensors of the same entity.
     *
     * @param  Collection<int, Sensor>  $sensors
     * @return Collection<int, Sensor>
     */
    protected function withCiscoEntitySensors(Collection $sensors): Collection
    {
        $cisco = $this->discoverCiscoEntitySensors();
        $keys = $cisco->map(fn (Sensor $sensor) => "$sensor->sensor_class-$sensor->sensor_index")->flip();

        return $cisco->merge($sensors->reject(fn (Sensor $sensor) => $sensor->sensor_type == 'entity-sensor'
            && $keys->has("$sensor->sensor_class-$sensor->sensor_index")));
    }

    /**
     * @return Collection<int, Sensor>
     */
    public function discoverCiscoEntitySensors(): Collection
    {
        $sensors = new Collection;
        $data = SnmpQuery::enumStrings()->walk([
            'CISCO-ENTITY-SENSOR-MIB::entSensorType',
            'CISCO-ENTITY-SENSOR-MIB::entSensorScale',
            'CISCO-ENTITY-SENSOR-MIB::entSensorValue',
            'CISCO-ENTITY-SENSOR-MIB::entSensorMeasuredEntity',
            'CISCO-ENTITY-SENSOR-MIB::entSensorPrecision',
        ])->table(1);

        if (empty($data)) {
            return $sensors;
        }

        /** @var CiscoEntities $entities */
        $entities = SnmpQuery::enumStrings()->walk([
            'ENTITY-MIB::entPhysicalDescr',
            'ENTITY-MIB::entPhysicalName',
            'ENTITY-MIB::entPhysicalClass',
            'ENTITY-MIB::entPhysicalContainedIn',
        ])->table(1);
        SnmpQuery::enumStrings()->walk('ENTITY-MIB::entAliasMappingIdentifier')->table(2, $entities);

        $thresholds = SnmpQuery::enumStrings()->walk([
            'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdSeverity',
            'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation',
            'CISCO-ENTITY-SENSOR-MIB::entSensorThresholdValue',
        ])->table(2);

        $ifIndexes = array_flip(SnmpQuery::walk('IF-MIB::ifName')->pluck());
        $transceivers = $this->getDevice()->transceivers()->pluck('index')->flip();

        foreach ($data as $index => $entry) {
            $value = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorValue'] ?? null;
            $class = $this->ciscoEntitySensorClass($entry['CISCO-ENTITY-SENSOR-MIB::entSensorType'] ?? null);

            if ($class === null || ! is_numeric($value) || ! is_numeric($index)) {
                continue;
            }

            // a cisco bug reports this precision
            $precision = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorPrecision'] ?? null;
            $scale = self::entitySensorScale($entry['CISCO-ENTITY-SENSOR-MIB::entSensorScale'] ?? null, $precision == 1615384784 ? null : $precision);
            if ($scale === null) {
                continue;
            }

            [$divisor, $multiplier] = $scale;
            $current = $value * $multiplier / $divisor;
            $measured = $entry['CISCO-ENTITY-SENSOR-MIB::entSensorMeasuredEntity'] ?? null;
            $descr = $this->ciscoEntitySensorDescr((int) $index, is_numeric($measured) ? (int) $measured : 0, $entities);

            if ($descr == '' || $current == -127) {
                continue;
            }

            $sensor = new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => $class->value,
                'sensor_oid' => ".1.3.6.1.4.1.9.9.91.1.1.1.1.4.$index",
                'sensor_index' => $index,
                'sensor_type' => 'cisco-entity-sensor',
                'sensor_descr' => ucwords($descr),
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_current' => $current,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => $measured,
                'rrd_type' => 'GAUGE',
            ]);

            $this->fillCiscoEntitySensorLimits($sensor, $class, $thresholds[$index] ?? []);
            $this->fillCiscoEntitySensorPort($sensor, (int) $index, $entities, $ifIndexes, $transceivers);

            $sensors->push($sensor);
        }

        return $sensors;
    }

    /**
     * The description of the sensor entity, prefixed by the entity it measures
     *
     * @param  CiscoEntities  $entities
     */
    private function ciscoEntitySensorDescr(int $index, int $measured, array $entities): string
    {
        $descr = Rewrite::entityDescr($this->ciscoEntitySensorName($index, $entities));

        if ($measured) {
            $descr = (($entities[$measured]['ENTITY-MIB::entPhysicalName'] ?? null) ?: $entities[$measured]['ENTITY-MIB::entPhysicalDescr'] ?? '') . ' - ' . $descr;
        }

        return trim(str_ireplace(' temperature', '', str_replace('Temp: ', '', $descr)));
    }

    /**
     * The name of the sensor entity
     *
     * @param  CiscoEntities  $entities
     */
    protected function ciscoEntitySensorName(int $index, array $entities): string
    {
        return ($entities[$index]['ENTITY-MIB::entPhysicalName'] ?? null) ?: $entities[$index]['ENTITY-MIB::entPhysicalDescr'] ?? '';
    }

    private function ciscoEntitySensorClass(?string $type): ?SensorType
    {
        return match ($type) {
            'voltsDC', 'voltsAC' => SensorType::Voltage,
            'amperes' => SensorType::Current,
            'watt', 'watts' => SensorType::Power,
            'hertz' => SensorType::Frequency,
            'percentRH' => SensorType::Humidity,
            'rpm' => SensorType::Fanspeed,
            'celsius' => SensorType::Temperature,
            'dBm' => SensorType::Dbm,
            default => null,
        };
    }

    /**
     * @param  array<int|string, CiscoThreshold>  $thresholds
     */
    private function fillCiscoEntitySensorLimits(Sensor $sensor, SensorType $class, array $thresholds): void
    {
        $limits = [];

        foreach ($thresholds as $threshold) {
            $value = $threshold['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdValue'] ?? null;
            if ($value === null || $value == -32768 || $value == 2147483647 || ($class === SensorType::Fanspeed && $value == -1)) {
                continue;
            }

            $high = in_array($threshold['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation'] ?? null, ['greaterOrEqual', 'greaterThan']);
            $low = in_array($threshold['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdRelation'] ?? null, ['lessOrEqual', 'lessThan']);
            $limit = match ($threshold['CISCO-ENTITY-SENSOR-MIB::entSensorThresholdSeverity'] ?? null) {
                'major', 'critical' => $high ? 'sensor_limit' : ($low ? 'sensor_limit_low' : null),
                'minor' => $high ? 'sensor_limit_warn' : ($low ? 'sensor_limit_low_warn' : null),
                'other' => $high ? 'other' : ($low ? 'other_low' : null),
                default => null,
            };

            // a 0 threshold is ignored when the other of major and critical is set
            if ($limit === null || ($value == 0 && isset($limits[$limit]) && in_array($limit, ['sensor_limit', 'sensor_limit_low']))) {
                continue;
            }

            $limits[$limit] = $value * $sensor->sensor_multiplier / $sensor->sensor_divisor;
        }

        // some platforms (ISR) only have the other severity, use it as the critical limit
        if (! isset($limits['sensor_limit_low']) && ! isset($limits['sensor_limit_low_warn']) && isset($limits['other_low'])) {
            $limits['sensor_limit_low'] = $limits['other_low'];
        }

        // many temperature sensors have no low limits, a 0 low limit is not useful either
        if ($class === SensorType::Temperature) {
            $limits['sensor_limit_low_warn'] = ($limits['sensor_limit_low_warn'] ?? 0) == 0 ? -1 : $limits['sensor_limit_low_warn'];
            $limits['sensor_limit_low'] = ($limits['sensor_limit_low'] ?? 0) == 0 ? -5 : $limits['sensor_limit_low'];
        }
        if (! isset($limits['sensor_limit']) && ! isset($limits['sensor_limit_warn']) && isset($limits['other'])) {
            $limits['sensor_limit'] = $limits['other'];
        }

        unset($limits['other'], $limits['other_low']);
        $sensor->fill($limits);
    }

    /**
     * Link sensors of transceivers and ports to the port entity, found through the entities containing the sensor
     *
     * @param  CiscoEntities  $entities
     * @param  array<string, int|string>  $ifIndexes  ifIndex by ifName
     * @param  Collection<int|string, int>  $transceivers  entity indexes of transceivers
     */
    private function fillCiscoEntitySensorPort(Sensor $sensor, int $index, array $entities, array $ifIndexes, Collection $transceivers): void
    {
        $parent = $entities[$index]['ENTITY-MIB::entPhysicalContainedIn'] ?? 0;
        $ifIndex = 0;

        while ($parent != 0 && $parent != $index && isset($entities[$parent])) {
            if ($transceivers->has($parent)) {
                $sensor->fill(['entPhysicalIndex' => $parent, 'entPhysicalIndex_measured' => 'ports', 'group' => 'transceiver']);

                return;
            }

            $class = $entities[$parent]['ENTITY-MIB::entPhysicalClass'] ?? null;
            if ($class === 'port') {
                if (preg_match('/ifIndex[.\[](\d+)/', (string) ($entities[$parent][0]['ENTITY-MIB::entAliasMappingIdentifier'] ?? ''), $matches)) {
                    $ifIndex = (int) $matches[1];
                }
                break;
            }

            if ($class === 'module' && isset($ifIndexes[$entities[$parent]['ENTITY-MIB::entPhysicalName'] ?? ''])) {
                $ifIndex = (int) $ifIndexes[$entities[$parent]['ENTITY-MIB::entPhysicalName']];
                break;
            }

            $parent = $entities[$parent]['ENTITY-MIB::entPhysicalContainedIn'] ?? 0;
        }

        // the port page finds the sensors of a port by its ifIndex
        if ($ifIndex != 0 && PortCache::getIdFromIfIndex($ifIndex, $this->getDevice())) {
            $sensor->fill(['entPhysicalIndex' => $ifIndex, 'entPhysicalIndex_measured' => 'ports', 'group' => 'transceiver']);
        }
    }
}
