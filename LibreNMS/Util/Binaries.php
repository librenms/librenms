<?php

/**
 * Binaries.php
 *
 * Location of external programs on this node.
 * Set in the Laravel config (librenms.binaries), usually by an environment variable,
 * otherwise searched for in the PATH and the standard bin directories.
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

class Binaries
{
    /** searched after PATH, which may be empty when running from cron or systemd */
    private const SEARCH_DIRS = ['/usr/local/sbin', '/usr/local/bin', '/usr/sbin', '/usr/bin', '/sbin', '/bin'];

    /**
     * Full path to the binary, or just the name if it was not found
     */
    public static function path(string $binary): string
    {
        return self::find($binary) ?? $binary;
    }

    /**
     * Full path to the binary, or null if it was not found
     */
    public static function find(string $binary): ?string
    {
        $configured = config("librenms.binaries.$binary");
        if (is_string($configured) && $configured !== '') {
            return str_starts_with($configured, '/') && self::isExecutableFile($configured) ? $configured : null;
        }

        foreach (self::searchDirs() as $dir) {
            $file = rtrim($dir, '/') . '/' . $binary;
            if (self::isExecutableFile($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Directories searched for binaries, in order
     *
     * @return string[]
     */
    public static function searchDirs(): array
    {
        $path_dirs = explode(PATH_SEPARATOR, (string) getenv('PATH'));

        // skip relative entries such as an empty one, which would search the working directory
        return array_values(array_unique(array_filter([...$path_dirs, ...self::SEARCH_DIRS], fn (string $dir): bool => str_starts_with($dir, '/'))));
    }

    private static function isExecutableFile(string $path): bool
    {
        return @is_file($path) && @is_executable($path);
    }
}
