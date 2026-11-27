<?php

/**
 * LegacySensorDiscovery.php
 *
 * Run the per-OS sensor discovery includes that are not converted yet
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

use App\Discovery\Sensor as SensorCollector;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\YamlDiscoveryDefinition;
use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Debug;

/**
 * Temporary bridge while sensor discovery includes are converted to OS classes and yaml.
 * Remove it with includes/discovery/sensors and the legacy discover_sensor() function.
 */
trait LegacySensorDiscovery
{
    /** @var array<string, mixed>|null */
    private ?array $legacyPreCacheData = null;

    /**
     * The sensors discovered by legacy includes, in the order they ran before.
     *
     * @return Collection<int, Sensor>
     */
    protected function discoverLegacySensors(): Collection
    {
        $files = $this->legacySensorFiles();
        if (empty($files)) {
            return new Collection;
        }

        include_once base_path('includes/functions.php');
        include_once base_path('includes/common.php');
        include_once base_path('includes/dbFacile.php');
        include_once base_path('includes/discovery/functions.inc.php');

        $collector = new SensorCollector($this->getDevice());
        app()->instance('sensor-discovery', $collector);
        Debug::disableErrorReporting(); // ignore errors in legacy code

        try {
            $os = $this;
            $device = &$this->getDeviceArray();
            $pre_cache = $this->legacySensorPreCache(true);

            foreach ($files as $file) {
                include $file;
            }
        } finally {
            Debug::enableErrorReporting();
            app()->forgetInstance('sensor-discovery');
        }

        return $collector->getModels()->each(function (Sensor $sensor) use ($collector): void {
            if ($sensor->sensor_class == 'state') {
                $sensor->withStates($collector->getStateTranslations($sensor->sensor_type) ?? []);
            }
        });
    }

    /**
     * Data of the legacy sensor pre-cache include, optionally with the data of all yaml modules
     *
     * @return array<string, mixed>
     */
    protected function legacySensorPreCache(bool $withYaml = false): array
    {
        $pre_cache = $this->legacyPreCacheData ?? [];
        $file = base_path("includes/discovery/sensors/pre-cache/{$this->getName()}.inc.php");

        if ($this->legacyPreCacheData === null && is_file($file)) {
            $os = $this;
            $device = $this->getDeviceArray();
            Debug::disableErrorReporting();
            try {
                include $file;
            } finally {
                Debug::enableErrorReporting();
            }
        }
        $this->legacyPreCacheData = $pre_cache;

        if ($withYaml && $this->getName() !== 'avtech') {
            $sections = [];
            foreach ($this->getDiscovery()['modules'] ?? [] as $data) {
                foreach ($data as $key => $section) {
                    if ($key === 'data') {
                        $sections[] = ['data' => $section];
                    } elseif (isset($section['data'])) {
                        $sections[] = $section;
                    }
                }
            }

            $pre_cache += YamlDiscoveryDefinition::walkAll($sections);
        }

        return $pre_cache;
    }

    /**
     * @return list<string>
     */
    private function legacySensorFiles(): array
    {
        $device = $this->getDeviceArray();
        $dir = base_path('includes/discovery/sensors');
        $files = [];

        foreach (SensorType::values() as $class) {
            if (! empty($device['os_group'])) {
                $files[] = "$dir/$class/{$device['os_group']}.inc.php";
            }
            $files[] = "$dir/$class/{$device['os']}.inc.php";
        }

        return array_values(array_filter($files, is_file(...)));
    }
}
