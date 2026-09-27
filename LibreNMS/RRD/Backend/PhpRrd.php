<?php

/**
 * PhpRrd.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\RRD\Backend;

use LibreNMS\Exceptions\RrdException;
use Log;

class PhpRrd implements RrdBackendInterface
{
    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     *
     * @internal
     */
    public function create(string $filename, array $data): void
    {
        Log::debug('PHPRRD[%gcreate ' . implode(' ', $data) . '%n]', ['color' => true]);
        if (! rrd_create($filename, $data)) {
            Log::warning('Error creating RRD file: ' . rrd_error());
        }
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     */
    public function update(string $filename, array $data): void
    {
        $data = ['N:' . implode(':', array_map(fn ($v) => is_numeric($v) ? $v : 'U', $data))];
        Log::debug("PHPRRD[%gupdate $filename " . implode(' ', $data) . '%n]', ['color' => true]);

        // The \RRDUpdater class does not use rrdcached, so we need to use the function
        if (! rrd_update($filename, $data)) {
            throw RrdException::parse(rrd_error());
        }
    }

    public function last(string $filename): string
    {
        Log::debug("PHPRRD[%glast $filename%n]", ['color' => true]);
        $last = rrd_last($filename);
        if (! $last) {
            return "$filename: No such file or directory";
        }

        return (string) $last;
    }

    /**
     * @param  string|string[]  $prefix
     * @return string[]
     */
    public function list(string $dir, string|array $prefix): array
    {
        $ret = array_diff(scandir($dir), ['.', '..']);

        return array_filter($ret, fn ($file) => str_starts_with((string) $file, $prefix));
    }
}
