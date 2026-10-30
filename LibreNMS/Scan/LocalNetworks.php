<?php

/**
 * LocalNetworks.php
 *
 * The networks this server is connected to, suggested as networks to scan.
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

use LibreNMS\Util\IPv4;

class LocalNetworks
{
    /** interfaces of containers, VMs and VPNs, their networks are not where the devices are */
    private const VIRTUAL_INTERFACES = '/^(lo|docker|br-|veth|virbr|vnet|cni|flannel|cali|vxlan|podman|lxc|tailscale|zt|wg)/';
    /** a larger network is narrowed to the /24 of the server, a large scan is slow */
    private const MIN_PREFIX = 22;
    /** the address family of IPv4, AF_INET needs the sockets extension */
    private const AF_INET = 2;

    /**
     * @param  string[]  $containerMarkers  files that exist inside a container (Docker, Podman)
     */
    public function __construct(
        private readonly array $containerMarkers = ['/.dockerenv', '/run/.containerenv'],
    ) {
    }

    /**
     * The IPv4 networks of the interfaces that are up, without duplicates.
     * None inside a container, its interfaces are on the container network, not the LAN.
     *
     * @param  array<string, array{unicast?: list<array{family?: int, address?: string, netmask?: string}>, up?: bool}>|null  $interfaces  as net_get_interfaces() returns, null to read them
     * @return string[] in CIDR notation
     */
    public function find(?array $interfaces = null): array
    {
        if ($this->inContainer()) {
            return [];
        }

        $interfaces ??= net_get_interfaces() ?: [];

        $networks = [];
        foreach ($interfaces as $name => $interface) {
            if (empty($interface['up']) || preg_match(self::VIRTUAL_INTERFACES, (string) $name)) {
                continue;
            }

            foreach ($interface['unicast'] ?? [] as $address) {
                if (($address['family'] ?? null) !== self::AF_INET || ! isset($address['address'], $address['netmask'])) {
                    continue;
                }

                $network = $this->network($address['address'], $address['netmask']);
                if ($network !== null) {
                    $networks[$network] = $network;
                }
            }
        }

        return array_values($networks);
    }

    private function inContainer(): bool
    {
        foreach ($this->containerMarkers as $marker) {
            if (file_exists($marker)) {
                return true;
            }
        }

        return false;
    }

    private function network(string $address, string $netmask): ?string
    {
        if (! IPv4::isValid($address)) {
            return null;
        }

        $cidr = IPv4::netmask2cidr($netmask);
        $ip = new IPv4($address);

        // a host or point-to-point address has no other devices, loopback and link-local are not reachable
        if ($cidr >= 31 || $ip->inNetwork('127.0.0.0/8') || $ip->inNetwork('169.254.0.0/16')) {
            return null;
        }

        return $ip->getNetwork($cidr < self::MIN_PREFIX ? 24 : $cidr);
    }
}
