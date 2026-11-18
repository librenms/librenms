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
use LibreNMS\Util\Rewrite;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;

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

            // the socket can exist while rrdcached is stopped, or with permissions this user cannot connect with
            $error = self::connect("unix://$socket");
            if ($error !== null) {
                return ValidationResult::fail(
                    trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_socket_connect', ['socket' => $socket, 'user' => Validator::getUsername(), 'error' => $error]),
                    trans('validation.validations.rrd.CheckRrdcachedConnectivity.fix_socket_connect', ['group' => config('librenms.group')])
                );
            }
        } else {
            [$host, $port] = self::hostAndPort($rrdcached);
            if (self::connect('tcp://' . Rewrite::addIpv6Brackets($host) . ":$port") !== null) {
                return ValidationResult::fail(trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_port', ['server' => $host, 'port' => $port]));
            }
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdcachedConnectivity.ok'));
    }

    /**
     * Open and close a connection, returns the error or null on success
     */
    private static function connect(string $address): ?string
    {
        $connection = @stream_socket_client($address, $errno, $errstr, 5);
        if (! is_resource($connection)) {
            return $errstr ?: "error $errno";
        }

        fclose($connection);

        return null;
    }

    /**
     * Split host, host:port, [ipv6], [ipv6]:port or a bare ipv6 address like librrd, defaulting to port 42217
     *
     * @return array{string, int}
     */
    private static function hostAndPort(string $address): array
    {
        if (preg_match('/^\[(.+)](?::(\d+))?$/', $address, $matches)) {
            return [$matches[1], (int) ($matches[2] ?? 0) ?: 42217];
        }

        // more than one colon is a bare ipv6 address without a port
        if (substr_count($address, ':') === 1) {
            [$host, $port] = explode(':', $address);

            return [$host, (int) $port ?: 42217];
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
