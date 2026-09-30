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

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use Log;

class PhpRrd implements RrdBackendInterface
{
    private ?RrdcachedSocketCmd $rrdcachedSocket = null;
    private readonly string $rrdcached;

    public function __construct()
    {
        // Create a RrdtoolRrd to fall through to
        $this->rrdcached = LibrenmsConfig::get('rrdcached', '');
    }

    /**
     * Close rrdcachedSocket
     */
    public function _destruct(): void
    {
    }

    private function socketCmd(): RrdcachedSocketCmd
    {
        // Only connect once
        $this->rrdcachedSocket ??= new RrdcachedSocketCmd();

        return $this->rrdcachedSocket;
    }

    /**
     * @param  string[]  $data
     *
     * @throws RrdException
     *
     * @internal
     */
    public function create(string $filename, array $data): void
    {
        if ($this->rrdcached) {
            $data = ['-d', $this->rrdcached, ...$data];
        }

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
        if ($this->rrdcached) {
            $data = ['-d', $this->rrdcached, ...$data];
        }

        Log::debug("PHPRRD[%gupdate $filename " . implode(' ', $data) . '%n]', ['color' => true]);

        // The \RRDUpdater class does not use rrdcached, so we need to use the function
        if (! rrd_update($filename, $data)) {
            throw RrdException::parse(rrd_error());
        }
    }

    public function last(string $filename): string
    {
        if ($this->rrdcached) {
            // PHP-RRD does not support this command - use a direct connection to rrdcached
            return $this->socketCmd()->last($filename);
        }

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
        if ($this->rrdcached) {
            $ret = $this->socketCmd()->list($dir);
        } else {
            // No cached - it's just a single level file list, so we can use scandir
            $ret = array_diff(scandir($dir), ['.', '..']);
        }

        return array_filter($ret, fn ($file) => str_starts_with((string) $file, $prefix));
    }

    /**
     * @param  string[]  $options
     */
    public function graph(array $options): string
    {
        $savedEnv = $this->saveEnv();

        Log::debug('PHPRRD[%graph ' . implode(' ', $options) . '%n]', ['color' => true]);
        $rrd = new \RRDGraph('-');
        $rrd->setOptions($options);
        try {
            $data = $rrd->saveVerbose();
        } catch (\Exception $e) {
            throw new RrdGraphException($e->getMessage());
        } finally {
            $this->restoreEnv($savedEnv);
        }

        return $data['image'];
    }

    /**
     * @param  string[]  $savedEnv
     */
    private function restoreEnv($savedEnv): void
    {
        foreach ($savedEnv as $key => $value) {
            if ($value) {
                putenv("$key=$value"); // Restore environment variable
            } else {
                putenv($key); // Remove environment variable
            }
        }
    }

    /**
     * @return string[]
     */
    private function saveEnv(): array
    {
        $ret = [
            'LC_ALL' => getenv('LC_ALL'),
            'TZ' => ('TZ'),
            'RRDCACHED_ADDRESS' => ('RRDCACHED_ADDRESS'),
        ];

        putenv('LC_ALL=C'); // force english/standard output
        if ($this->rrdcached) {
            putenv('RRDCACHED_ADDRESS=' . $this->rrdcached);
        }
        if (session('preferences.timezone')) {
            putenv('TZ=' . session('preferences.timezone'));
        }

        return $ret;
    }
}
