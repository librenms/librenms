<?php

/**
 * Netgear.php
 *
 * Netgear switches
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

namespace LibreNMS\OS;

use App\Models\Port;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\PortFinder;
use LibreNMS\Interfaces\Discovery\NeighborPortResolution;
use LibreNMS\OS;

class Netgear extends OS implements NeighborPortResolution
{
    /**
     * Some Netgear switches (GS108Tv1) advertise port "g1", but name the port "Port 1 Gigabit Ethernet", map it to the ifIndex
     */
    public function findNeighborPort(Neighbor $neighbor, PortFinder $ports): ?Port
    {
        if ($this->getDevice()->sysDescr == 'GS108T'
            && $neighbor->sysDescr == 'Smart Switch'
            && preg_match('/^g(\d+)$/', $neighbor->portId, $matches)) {
            return $ports->byIfIndex($matches[1]);
        }

        return null;
    }
}
