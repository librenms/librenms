<?php

/**
 * YamlSensorDiscovery.php
 *
 * Discover sensors defined in the os discovery yaml
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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\SensorYamlDiscoveryDefinition;
use LibreNMS\Discovery\YamlDiscoveryDefinition;
use LibreNMS\Enum\SensorType;

trait YamlSensorDiscovery
{
    /**
     * @return Collection<int, Sensor>
     */
    public function discoverYamlSensors(): Collection
    {
        $yaml = $this->getDiscovery('sensors');
        $sensors = new Collection;

        if (empty($yaml)) {
            return $sensors;
        }

        // items may use data walked for other items and sensor classes
        $walked = $this->walkSensorYaml($yaml);

        foreach (SensorType::cases() as $type) {
            $section = $yaml[$type->value] ?? [];
            if (empty($section['data'])) {
                continue;
            }

            // the value of an item defaults to its oid
            $section['data'] = array_map(fn (array $item) => isset($item['oid']) ? $item + ['value' => $item['oid']] : $item, $section['data']);

            $sensors = $sensors->merge(new SensorYamlDiscoveryDefinition($type, $section['options'] ?? [])->discover($section, [
                'poller_type' => 'snmp',
                'sensor_class' => $type->value,
                'sensor_type' => $this->getName(),
            ], $walked));
        }

        return $sensors;
    }

    /**
     * Walk the oids of the sensor yaml, the OS may add data for the yaml to use.
     *
     * @param  array<string, array{data?: list<array<string, mixed>>}>  $yaml
     * @return array<string, array<int|string, mixed>>
     */
    protected function walkSensorYaml(array $yaml): array
    {
        return YamlDiscoveryDefinition::walkAll($yaml, $this->yamlOids());
    }

    /**
     * The oids all modules of the discovery yaml walk
     *
     * @return string[]
     */
    protected function yamlOids(): array
    {
        $oids = [];
        foreach ($this->getDiscovery()['modules'] ?? [] as $module) {
            foreach ((array) $module as $key => $section) {
                foreach (($key === 'data' ? $section : ($section['data'] ?? [])) as $item) {
                    array_push($oids, ...Arr::wrap(is_array($item) ? $item['oid'] ?? [] : []));
                }
            }
        }

        return array_values(array_unique($oids));
    }
}
