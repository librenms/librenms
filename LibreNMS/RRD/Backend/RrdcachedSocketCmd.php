<?php

/**
 * RrdcachedSocketCmd.php
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
use LibreNMS\Util\Debug;
use Log;

class RrdcachedSocketCmd
{
    /** @var resource */
    private $socket;

    public function __construct()
    {
        // Create a RrdtoolRrd to fall through to
        $rrdcached = LibrenmsConfig::get('rrdcached', '');

        if (! $rrdcached) {
            throw new \Exception('SocketRrd only works with rrdcached');
        }

        if (str_starts_with($rrdcached, 'unix:/')) {
            $this->socket = stream_socket_client(str_replace('unix:/', 'unix:///', $rrdcached), $errno, $errstr, 30);
        } else {
            $this->socket = stream_socket_client('tcp://' . $rrdcached, $errno, $errstr, 30);
        }

        if (! $this->socket) {
            throw new \Exception('Error connecting to rrdcached: ' . $errstr);
        }

        // 60 second timeout per command
        stream_set_timeout($this->socket, 60, 0);
    }

    /**
     * Close rrdcachedSocket
     */
    public function _destruct(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    public function last(string $filename): string
    {
        return $this->command("LAST $filename");
    }

    /**
     * @return string[]
     */
    public function list(string $dir): array
    {
        $cmd = "LIST $dir";
        Log::debug("SRRD[%g$cmd%n]", ['color' => true]);
        fwrite($this->socket, "$cmd\n");
        $line = fgets($this->socket);

        if (! preg_match('/(-?\d+) (.+)/', $line, $matches)) {
            throw new \Exception("Error reading data from rrdcached: $line");
        }

        if ((int) $matches[1] < 0 || $matches[2] != 'RRDs') {
            throw RrdException::parse($matches[2]);
        }

        $ret = [];
        for ($i = 0; $i < (int) $matches[1]; $i++) {
            $ret[] = trim(fgets($this->socket));
        }

        return $ret;
    }

    private function command(string $cmd, bool $ignoreErrors = false): string
    {
        Log::debug("SRRD[%g$cmd%n]", ['color' => true]);
        fwrite($this->socket, "$cmd\n");
        $line = fgets($this->socket);

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
