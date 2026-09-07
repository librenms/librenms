<?php

/**
 * Mib.php
 *
 * Helper for MIB directories and configuration.
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

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Device;

class Mib
{
    /**
     * Get the list of MIB directories for a device, including OS and group directories.
     *
     * @param  Device|null  $device
     * @param  array<int, string>  $extraDirs
     * @return array<int, string>
     */
    public static function directories(?Device $device = null, array $extraDirs = []): array
    {
        $base = LibrenmsConfig::get('mib_dir');
        $dirs = [$base];

        if ($device) {
            // os group
            if ($osGroup = LibrenmsConfig::getOsSetting($device->os, 'group')) {
                if (file_exists("$base/$osGroup")) {
                    $dirs[] = "$base/$osGroup";
                }
            }

            // os directory
            $osMibdir = LibrenmsConfig::getOsSetting($device->os, 'mib_dir');
            if ($osMibdir && is_string($osMibdir)) {
                $dirs[] = "$base/$osMibdir";
            } elseif (file_exists($base . '/' . $device->os)) {
                $dirs[] = $base . '/' . $device->os;
            }
        }

        foreach ($extraDirs as $mibDir) {
            $dirs[] = str_starts_with((string) $mibDir, '/') ? (string) $mibDir : "$base/$mibDir";
        }

        return array_values(array_unique(array_filter(array_map(fn ($dir) => rtrim((string) $dir, '/'), $dirs))));
    }
}
