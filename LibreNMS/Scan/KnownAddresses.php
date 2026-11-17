<?php

/**
 * KnownAddresses.php
 *
 * Find the addresses that already belong to a device.
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

namespace LibreNMS\Scan;

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use LibreNMS\Util\IP;

class KnownAddresses
{
    /**
     * The addresses that are a device hostname, a device IP, or a port address of a device.
     * The same as Device::findByIp(), for many addresses at once.
     *
     * @param  string[]  $ips
     * @return array<string, string> the device hostname by address
     */
    public function find(array $ips): array
    {
        // the database has uncompressed addresses
        $addresses = [];
        foreach ($ips as $ip) {
            $addresses[IP::parse($ip)->uncompressed()] = $ip;
        }
        $uncompressed = array_keys($addresses);

        $hostnames = Device::whereIn('hostname', $uncompressed)->pluck('hostname', 'hostname');

        $deviceIps = Device::whereIn('ip', array_map(inet_pton(...), $ips))->get(['hostname', 'ip'])
            ->mapWithKeys(fn (Device $device) => [IP::parse((string) $device->ip)->uncompressed() => $device->hostname]);

        $portIps = Ipv4Address::whereIn('ipv4_address', $uncompressed)
            ->join('ports', 'ports.port_id', 'ipv4_addresses.port_id')
            ->join('devices', 'devices.device_id', 'ports.device_id')
            ->pluck('devices.hostname', 'ipv4_address')
            ->union(Ipv6Address::whereIn('ipv6_address', $uncompressed)
                ->join('ports', 'ports.port_id', 'ipv6_addresses.port_id')
                ->join('devices', 'devices.device_id', 'ports.device_id')
                ->pluck('devices.hostname', 'ipv6_address'));

        $known = [];
        foreach ($hostnames->union($deviceIps)->union($portIps) as $address => $hostname) {
            if (isset($addresses[$address])) {
                $known[$addresses[$address]] = (string) $hostname;
            }
        }

        return $known;
    }
}
