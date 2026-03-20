<?php

/**
 * Module.php
 *
 * -Description-
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Modules\LegacyModule;
use LibreNMS\RRD\RrdDefinition;

class Module
{
    public static function exists(string $module_name): bool
    {
        if (class_exists(StringHelpers::toClass($module_name, '\\LibreNMS\\Modules\\'))) {
            return true;
        }

        return LibrenmsConfig::has('discovery_modules.' . $module_name) || LibrenmsConfig::has('poller_modules.' . $module_name);
    }

    public static function fromName(string $module_name): \LibreNMS\Interfaces\Module
    {
        $module_class = StringHelpers::toClass($module_name, '\\LibreNMS\\Modules\\');

        return class_exists($module_class) ? new $module_class : new LegacyModule($module_name);
    }

    public static function legacyDiscoveryExists(string $module_name): bool
    {
        return is_file(base_path("includes/discovery/$module_name.inc.php"));
    }

    public static function legacyPollingExists(string $module_name): bool
    {
        return is_file(base_path("includes/polling/$module_name.inc.php"));
    }

    public static function savePerformance(string $module, ProcessType $type, float $start_time, int $start_memory): void
    {
        $module_time = microtime(true) - $start_time;
        $module_mem = (memory_get_usage() - $start_memory);

        Log::info(sprintf(">> Runtime for %s module '%s': %.4f seconds with %s bytes", $type->name, $module, $module_time, $module_mem));

        if ($type == ProcessType::Discovery) {
            return; // do not record for now as there is currently no rrd during discovery
        }

        app('Datastore')->put(DeviceCache::getPrimary()->toArray(), 'poller-perf', [
            'module' => $module,
            'rrd_def' => RrdDefinition::make()->addDataset('poller', 'GAUGE', 0),
            'rrd_name' => ['poller-perf', $module],
        ], [
            'poller' => $module_time,
        ]);
    }
}
