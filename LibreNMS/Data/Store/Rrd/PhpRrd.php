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

namespace LibreNMS\Data\Store\Rrd;

use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Util\Debug;
use Log;

class PhpRrd implements RrdBackendInterface
{
    /** @var resource|null */
    private $rrdcachedSocket = null;
    private readonly string $rrdcached;

    public function __construct()
    {
        // Create a RrdtoolRrd to fall through to
        $this->rrdcached = LibrenmsConfig::get('rrdcached', '');

        putenv('LC_ALL=C'); // force english/standard output
        if ($this->rrdcached) {
            putenv('RRDCACHED_ADDRESS=' . $this->rrdcached);
        }
        if (session('preferences.timezone')) {
            putenv('TZ=' . session('preferences.timezone'));
        }
    }

    /**
     * Close rrdcachedSocket
     */
    public function terminate(): void
    {
        if ($this->rrdcachedSocket) {
            fclose($this->rrdcachedSocket);
        }
    }

    private function rrdcachedSocketConnect(): void
    {
        // Only connect once
        if ($this->rrdcachedSocket) {
            return;
        }

        if (! $this->rrdcached) {
            throw new \Exception('SocketRrd only works with rrdcached');
        }

        if (str_starts_with($this->rrdcached, 'unix:/')) {
            $this->rrdcachedSocket = stream_socket_client(str_replace('unix:/', 'unix:///', $this->rrdcached), $errno, $errstr, 30);
        } else {
            $this->rrdcachedSocket = stream_socket_client('tcp://' . $this->rrdcached, $errno, $errstr, 30);
        }

        if (! $this->rrdcachedSocket) {
            throw new \Exception('Error connecting to rrdcached: ' . $errstr);
        }

        // 60 second timeout per command
        stream_set_timeout($this->rrdcachedSocket, 60, 0);
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
        $data = 'N:' . implode(':', array_map(fn ($v) => is_numeric($v) ? $v : 'U', $data));
        Log::debug("PHPRRD[%gupdate $filename $data%n]", ['color' => true]);

        // The \RRDUpdater class does not use rrdcached, so we need to use the function
        if (! rrd_update($filename, [$data])) {
            throw RrdException::parse(rrd_error());
        }
    }

    public function last(string $filename): string
    {
        if ($this->rrdcached) {
            // PHP-RRD does not support this command - use a direct connection to rrdcached
            $this->rrdcachedSocketConnect();

            return $this->socketCmd("LAST $filename");
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
            $cmd = "LIST $dir";
            Log::debug("SRRD[%g$cmd%n]", ['color' => true]);
            fwrite($this->rrdcachedSocket, "$cmd\n");
            $line = fgets($this->rrdcachedSocket);

            if (! preg_match('/(-?\d+) (.+)/', $line, $matches)) {
                throw new \Exception("Error reading data from rrdcached: $line");
            }

            if ((int) $matches[1] < 0 || $matches[2] != 'RRDs') {
                throw RrdException::parse($matches[2]);
            }

            $ret = [];
            for ($i = 0; $i < (int) $matches[1]; $i++) {
                $ret[] = trim(fgets($this->rrdcachedSocket));
            }
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
        Log::debug('PHPRRD[%graph ' . implode(' ', $options) . '%n]', ['color' => true]);
        $rrd = new \RRDGraph('-');
        $rrd->setOptions($options);
        try {
            $data = $rrd->saveVerbose();
        } catch (\Exception $e) {
            throw new RrdGraphException($e->getMessage());
        }

        return $data['image'];
    }

    private function socketCmd(string $cmd, bool $ignoreErrors = false): string
    {
        Log::debug("SRRD[%g$cmd%n]", ['color' => true]);
        fwrite($this->rrdcachedSocket, "$cmd\n");
        $line = fgets($this->rrdcachedSocket);

        if (! preg_match('/(-?\d+) (.+)/', $line, $matches)) {
            throw new \Exception("Error reading data from rrdcached: $line");
        }

        if ((int) $matches[1] < 0 && ! $ignoreErrors) {
            throw RrdException::parse($matches[2]);
        }

        if (Debug::isVerbose() && $matches[2]) {
            Log::debug('rrdcached Output: ' . $matches[2]);
        }

        return $matches[2];
    }
}
