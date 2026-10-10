<?php

/**
 * Neighbor.php
 *
 * A neighbor learned from a discovery protocol (LLDP, CDP, FDP, etc.)
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

namespace LibreNMS\Discovery\Neighbors;

use LibreNMS\Enum\LldpPortIdSubtype;

/**
 * Values are already normalized, use NeighborParser to parse them from snmp
 */
final readonly class Neighbor
{
    /**
     * @param  string  $protocol  lldp, cdp, fdp, etc
     * @param  int|null  $localPortId  port_id of the port on this device the neighbor was seen on
     * @param  string  $sysName  the name the neighbor advertised for itself
     * @param  string  $sysDescr  the neighbor's description or software version
     * @param  string|null  $platform  the neighbor's hardware platform
     * @param  string|null  $managementIp  an ip address the neighbor may be reached at
     * @param  string|null  $chassisMac  the neighbor's chassis mac address as 12 hex characters
     * @param  string  $portId  the neighbor's port identifier, interpreted according to $portIdSubtype (mac addresses as 12 hex characters)
     * @param  string  $portDescr  the neighbor's port description
     * @param  string|null  $portMac  the neighbor's port mac address as 12 hex characters
     */
    public function __construct(
        public string $protocol,
        public ?int $localPortId,
        public string $sysName = '',
        public string $sysDescr = '',
        public ?string $platform = null,
        public ?string $managementIp = null,
        public ?string $chassisMac = null,
        public string $portId = '',
        public LldpPortIdSubtype $portIdSubtype = LldpPortIdSubtype::InterfaceName,
        public string $portDescr = '',
        public ?string $portMac = null,
    ) {
    }
}
