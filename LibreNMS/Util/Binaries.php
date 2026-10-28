<?php

/**
 * Binaries.php
 *
 * Location of external programs on this node.
 * Set in the Laravel config (librenms.binaries), usually by an environment variable,
 * then a setting in the LibreNMS config (deprecated, will be removed),
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

use App\Facades\LibrenmsConfig;

class Binaries
{
    /** searched after PATH, which may be empty when running from cron or systemd */
    private const SEARCH_DIRS = ['/usr/local/sbin', '/usr/local/bin', '/usr/sbin', '/usr/bin', '/sbin', '/bin'];
    /** binaries that could be set in the LibreNMS config and their setting, support for these settings will be removed */
    public const LEGACY_SETTINGS = [
        'fping' => 'fping',
        'fping6' => 'fping6',
        'ipmitool' => 'ipmitool',
        'nfdump' => 'nfdump',
        'rrdtool' => 'rrdtool',
        'sendmail' => 'email_sendmail_path',
        'snmpbulkwalk' => 'snmpbulkwalk',
        'snmpget' => 'snmpget',
        'snmpgetnext' => 'snmpgetnext',
        'snmptranslate' => 'snmptranslate',
        'snmptrap' => 'snmptrap',
        'snmpwalk' => 'snmpwalk',
        'virsh' => 'virsh',
    ];

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

    /**
     * Full path set in the Laravel config (usually .env), or null if not set
     */
    public function configured(string $binary): ?string
    {
        $configured = config("librenms.binaries.$binary");

        return is_string($configured) && $configured !== '' ? $configured : null;
    }

    /**
     * Full path set in the LibreNMS config, if it is an executable file.
     * Support for these settings will be removed, they should move to .env
     */
    public function legacySetting(string $binary): ?string
    {
        if (! isset(self::LEGACY_SETTINGS[$binary])) {
            return null;
        }

        $setting = LibrenmsConfig::get(self::LEGACY_SETTINGS[$binary]);

        return is_string($setting) && str_starts_with($setting, '/') && self::isExecutableFile($setting) ? $setting : null;
    }

    /**
     * Full path found in the PATH or the standard bin directories, or null if not found
     */
    public function search(string $binary): ?string
    {
        foreach ($this->searchDirs() as $dir) {
            $file = rtrim($dir, '/') . '/' . $binary;
            if (self::isExecutableFile($file)) {
                return $file;
            }
        }

        return null;
    }

    private function locate(string $binary): ?string
    {
        $configured = $this->configured($binary);
        if ($configured !== null) {
            return str_starts_with($configured, '/') && self::isExecutableFile($configured) ? $configured : null;
        }

        return $this->legacySetting($binary) ?? $this->search($binary);
    }

    private static function isExecutableFile(string $path): bool
    {
        return @is_file($path) && @is_executable($path);
    }
}
