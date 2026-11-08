<?php

/**
 * NmsLldpMib.php
 *
 * LLDP neighbor discovery via NMS-LLDP-MIB (BDCOM and derivatives)
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

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Util\IP;
use SnmpQuery;

trait NmsLldpMib
{
    /**
     * The table index does not follow the MIB, but lldpRemLocalPortNum contains the ifIndex
     *
     * @return Collection<int, Neighbor>
     */
    protected function discoverLldpNeighbors(): Collection
    {
        $remTable = SnmpQuery::hideMib()->walk('NMS-LLDP-MIB::lldpRemTable');

        if (! $remTable->isValid()) {
            return new Collection;
        }

        $addresses = SnmpQuery::hideMib()->walk('NMS-LLDP-MIB::lldpRemManAddr')->pluck();

        return $remTable->mapTable(function (array $entry, ...$index) use ($addresses) {
            $address = $addresses[implode('.', $index)] ?? null;

            return NeighborParser::fromLldpRemEntry(
                $entry,
                PortCache::getIdFromIfIndex($entry['lldpRemLocalPortNum'] ?? null, $this->getDeviceId()),
                $address === null ? null : IP::parse($address, true)?->compressed(),
            );
        });
    }
}
