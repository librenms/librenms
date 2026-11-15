<?php

/**
 * Hyperion.php
 *
 * Bitstream Hyperion
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Enum\LldpPortIdSubtype;
use LibreNMS\OS;
use SnmpQuery;

class Hyperion extends OS
{
    /**
     * Hyperion firmware reports LLDP neighbors in the vendor MIB-LLDP only,
     * fall back to LLDP-MIB if that table is empty.
     *
     * @return Collection<int, Neighbor>
     */
    public function discoverNeighbors(): Collection
    {
        $neighbors = $this->discoverVendorLldpNeighbors();

        return $neighbors->isNotEmpty() ? $neighbors : parent::discoverNeighbors();
    }

    /**
     * @return Collection<int, Neighbor>
     */
    private function discoverVendorLldpNeighbors(): Collection
    {
        return SnmpQuery::hideMib()->walk('MIB-LLDP::lldpStatusNeighborsInformationTable')
            ->mapTable(function (array $entry, $ifIndex = null, $medIndex = null): ?Neighbor {
                if ($medIndex === null) {
                    return null; // invalid index
                }

                $portId = NeighborParser::parseText($entry['lldpStatusNeighborsInformationPortId'] ?? '');
                $portMac = NeighborParser::parseMac($portId);

                return new Neighbor(
                    protocol: 'lldp',
                    localPortId: PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()),
                    sysName: NeighborParser::parseName($entry['lldpStatusNeighborsInformationSystemName'] ?? ''),
                    sysDescr: NeighborParser::parseText($entry['lldpStatusNeighborsInformationSystemDescription'] ?? ''),
                    chassisMac: NeighborParser::parseMac($entry['lldpStatusNeighborsInformationChassisId'] ?? null),
                    portId: $portId,
                    portIdSubtype: $portMac === null ? LldpPortIdSubtype::InterfaceName : LldpPortIdSubtype::MacAddress,
                    portDescr: NeighborParser::parseText($entry['lldpStatusNeighborsInformationPortDescription'] ?? ''),
                    portMac: $portMac,
                );
            })->filter()->values();
    }
}
