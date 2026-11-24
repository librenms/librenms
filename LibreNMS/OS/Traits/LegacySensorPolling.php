<?php

/**
 * LegacySensorPolling.php
 *
 * Run the per-OS sensor polling includes that users may still have
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Debug;

/**
 * Deprecated, support will be removed. LibreNMS no longer ships these includes, OS classes override pollSensors() instead.
 */
trait LegacySensorPolling
{
    /**
     * Legacy includes (includes/polling/sensors/<class>/<os or os_group>.inc.php) adjust the raw values of the SNMP sensors of their class.
     *
     * @param  Collection<int, Sensor>  $sensors
     * @param  array<int, mixed>  $values  raw values by sensor_id
     * @param  array<string, mixed>  $snmp_data  the polled values by oid
     * @return array<int, mixed> raw values by sensor_id
     */
    protected function pollLegacySensors(Collection $sensors, array $values, array $snmp_data): array
    {
        $device = $this->getDeviceArray();

        foreach ($sensors->groupBy('sensor_class') as $class => $classSensors) {
            $file = $this->legacySensorPollingFile((string) $class);
            if ($file === null) {
                continue;
            }

            Log::warning("Legacy sensor include $file is deprecated and support will be removed, convert it to YAML or the PHP class of the OS: https://docs.librenms.org/Developing/os/Health-Information/");

            include_once base_path('includes/functions.php');
            include_once base_path('includes/common.php');
            include_once base_path('includes/dbFacile.php');
            include_once base_path('includes/polling/functions.inc.php');
            Debug::disableErrorReporting(); // ignore errors in legacy code

            try {
                foreach ($classSensors as $sensor) {
                    /** @var Sensor $sensor */
                    $value = self::runLegacySensorPolling($file, $device, (string) $class, $sensor->getAttributes(), (string) ($values[$sensor->sensor_id] ?? ''), $snmp_data);
                    if ($value === null || $value === '') {
                        unset($values[$sensor->sensor_id]); // not read
                    } else {
                        $values[$sensor->sensor_id] = $value;
                    }
                }
            } finally {
                Debug::enableErrorReporting();
            }
        }

        return $values;
    }

    /**
     * The OS include is used before the os_group include
     */
    private function legacySensorPollingFile(string $class): ?string
    {
        $device = $this->getDeviceArray();
        $dir = base_path("includes/polling/sensors/$class");

        foreach ([$device['os'] ?? null, $device['os_group'] ?? null] as $name) {
            if ($name && is_file("$dir/$name.inc.php")) {
                return "$dir/$name.inc.php";
            }
        }

        return null;
    }

    /**
     * Only the variables the legacy poller gave the includes are in scope, the include changes $sensor_value
     *
     * @param  array<string, mixed>  $device
     * @param  array<string, mixed>  $sensor
     * @param  array<string, mixed>  $snmp_data
     */
    private static function runLegacySensorPolling(string $file, array $device, string $class, array $sensor, string $sensor_value, array $snmp_data): mixed
    {
        include $file;

        return $sensor_value;
    }
}
