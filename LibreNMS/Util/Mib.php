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
use Illuminate\Support\Str;

class Mib
{
    /**
     * Get the list of MIB directories for a device, including OS and group directories.
     *
     * @param  string  $os
     * @param  array<int, string>  $extraDirs
     * @return array<int, string>
     */
    public static function directories(string $os = '', array $extraDirs = []): array
    {
        $base = rtrim(LibrenmsConfig::get('mib_dir'), '/');
        $dirs = [$base];

        if ($os) {
            if ($osGroup = LibrenmsConfig::getOsSetting($os, 'group')) {
                $dirs[] = "$base/$osGroup";
            }

            $dirs[] = "$base/$os";
            $dirs[] = $base . '/' . LibrenmsConfig::getOsSetting($os, 'mib_dir');
        }

        foreach ($extraDirs as $mibDir) {
            $dirs[] = rtrim(Str::start($mibDir, "$base/"), '/');
        }

        return array_values(array_filter(array_unique($dirs), is_dir(...)));
    }

    public static function parseCliInput(string $mibs, array $existing = []): array
    {
        if ($mibs === '') {
            return $existing;
        }

        if (! str_starts_with($mibs, '+')) {
            return explode(':', $mibs);
        }

        return array_values(array_unique([...$existing, ...explode(':', substr($mibs, 1))]));
    }
}
