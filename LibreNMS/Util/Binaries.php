<?php

/**
 * Binaries.php
 *
 * Location of external programs on this node.
 * Set in the Laravel config (librenms.binaries), usually by an environment variable,
 * otherwise searched for in the PATH and the standard bin directories.
 * Registered as a scoped service, so locations are found again for each job or request.
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

    /** @var array<string, string|null> */
    private array $paths = [];

    /**
     * Full path to the binary, or just the name if it was not found
     */
    public function path(string $binary): string
    {
        return $this->find($binary) ?? $binary;
    }

    /**
     * Full path to the binary, or null if it was not found
     */
    public function find(string $binary): ?string
    {
        if (! array_key_exists($binary, $this->paths)) {
            $this->paths[$binary] = $this->locate($binary);
        }

        return $this->paths[$binary];
    }

    /**
     * Directories searched for binaries, in order
     *
     * @return string[]
     */
    public function searchDirs(): array
    {
        $path_dirs = explode(PATH_SEPARATOR, (string) getenv('PATH'));

        // skip relative entries such as an empty one, which would search the working directory
        return array_values(array_unique(array_filter([...$path_dirs, ...self::SEARCH_DIRS], fn (string $dir): bool => str_starts_with($dir, '/'))));
    }

    private function locate(string $binary): ?string
    {
        $configured = config("librenms.binaries.$binary");
        if (is_string($configured) && $configured !== '') {
            return str_starts_with($configured, '/') && self::isExecutableFile($configured) ? $configured : null;
        }

        foreach ($this->searchDirs() as $dir) {
            $file = rtrim($dir, '/') . '/' . $binary;
            if (self::isExecutableFile($file)) {
                return $file;
            }
        }

        return null;
    }

    private static function isExecutableFile(string $path): bool
    {
        return @is_file($path) && @is_executable($path);
    }
}
