<?php

/*
 * CheckRrdcachedConnectivity.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckRrdcachedConnectivity implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $rrdcached = (string) LibrenmsConfig::get('rrdcached');

        // librrd treats unix: and bare paths as unix sockets
        if (str_starts_with($rrdcached, 'unix:') || str_starts_with($rrdcached, '/')) {
            $socket = preg_replace('/^unix:/', '', $rrdcached);
            if (! file_exists($socket)) {
                return ValidationResult::fail(trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_socket', ['socket' => $socket]));
            }

            return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdcachedConnectivity.ok'));
        }

        [$host, $port] = self::hostAndPort($rrdcached);
        $connection = @stream_socket_client('tcp://' . (str_contains($host, ':') ? "[$host]" : $host) . ":$port", $errno, $errstr, 5);
        if (! is_resource($connection)) {
            return ValidationResult::fail(trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_port', ['server' => $host, 'port' => $port]));
        }

        fclose($connection);

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdcachedConnectivity.ok'));
    }

    /**
     * Split host, host:port, [ipv6], [ipv6]:port or a bare ipv6 address like librrd, defaulting to port 42217
     *
     * @return array{string, int}
     */
    private static function hostAndPort(string $address): array
    {
        if (preg_match('/^\[(.+)](?::(\d+))?$/', $address, $matches)) {
            return [$matches[1], (int) ($matches[2] ?? 42217)];
        }

        // more than one colon is a bare ipv6 address without a port
        if (substr_count($address, ':') === 1) {
            [$host, $port] = explode(':', $address);

            return [$host, (int) $port];
        }

        return [$address, 42217];
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return (bool) LibrenmsConfig::get('rrdcached');
    }
}
