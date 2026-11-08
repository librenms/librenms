<?php

/**
 * SubnetAddress.php
 *
 * An address configured on an interface, IPv4 or IPv6
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

namespace App\Http\Controllers\Search\Data;

use App\Models\Ipv4Address;
use App\Models\Ipv6Address;
use App\Models\Port;
use LibreNMS\Util\IP;

class SubnetAddress
{
    public function __construct(
        public readonly IP $ip,
        public readonly int $prefixLength,
        public readonly int $portId,
        public readonly ?Port $port,
    ) {
    }

    public static function from(Ipv4Address|Ipv6Address $address): ?self
    {
        $isIpv4 = $address instanceof Ipv4Address;
        $ip = IP::parse($isIpv4 ? $address->ipv4_address : $address->ipv6_address, true);

        return $ip === null ? null : new self($ip, (int) ($isIpv4 ? $address->ipv4_prefixlen : $address->ipv6_prefixlen), (int) $address->port_id, $address->port);
    }

    /**
     * The address with its prefix length
     */
    public function cidr(): string
    {
        return $this->ip->compressed() . '/' . $this->prefixLength;
    }

    /**
     * The network this address is in, in cidr format
     */
    public function network(): string
    {
        return $this->ip->getNetwork($this->prefixLength);
    }

    public function isOnNetworkDevice(): bool
    {
        return in_array($this->port?->device?->type, ['network', 'firewall']);
    }
}
